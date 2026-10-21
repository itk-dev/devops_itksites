<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Advisory;
use App\Entity\CodeOwner;
use App\Entity\DetectionResult;
use App\Entity\DockerImage;
use App\Entity\Domain;
use App\Entity\GitRepo;
use App\Entity\Installation;
use App\Entity\ModuleVersion;
use App\Entity\OIDC;
use App\Entity\SecurityContract;
use App\Entity\Server;
use App\Entity\ServiceCertificate;
use App\Entity\Site;
use App\Entity\User;
use App\Message\ProcessDetectionResult;
use App\MessageHandler\ProcessDetectionResultHandler;
use App\Types\DetectionType;
use App\Types\ServerTypeType;
use App\Types\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The fixtures model the content as it looks in production, with made-up
 * values: servers with their detection results, the installations and sites
 * built from them, packages, advisories, git repos, Docker images, Drupal
 * modules and the projects and contracts around them. These tests pin the
 * cases the admin has to handle, so a fixture change cannot quietly drop one.
 */
class FixturesTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        // Read what was stored, not the objects the loader left in memory,
        // whose inverse collections are not filled in.
        $this->entityManager->clear();
    }

    public function testEveryEntityHasFixtures(): void
    {
        foreach ($this->entityManager->getMetadataFactory()->getAllMetadata() as $metadata) {
            if ($metadata->isMappedSuperclass) {
                continue;
            }
            $this->assertGreaterThan(0, $this->entityManager->getRepository($metadata->getName())->count([]), $metadata->getName());
        }
    }

    public function testEveryDetectionTypeHasAResult(): void
    {
        $types = array_map(static fn (DetectionResult $result): string => $result->getType(), $this->entityManager->getRepository(DetectionResult::class)->findAll());

        $this->assertEqualsCanonicalizing(array_values(DetectionType::CHOICES), array_values(array_unique($types)));
    }

    public function testEveryServerTypeHasAServer(): void
    {
        $types = array_map(static fn (Server $server): string => (string) $server->getType(), $this->entityManager->getRepository(Server::class)->findAll());

        $this->assertEqualsCanonicalizing(array_values(ServerTypeType::CHOICES), array_values(array_unique($types)));
    }

    public function testSitesAreServedByDockerAndNginx(): void
    {
        $types = array_map(static fn (Site $site): string => (string) $site->getType(), $this->entityManager->getRepository(Site::class)->findAll());

        $this->assertEqualsCanonicalizing([SiteType::DOCKER, SiteType::NGINX], array_values(array_unique($types)));
    }

    /**
     * The latest dir result decides which installations a server has, so a
     * replay of the fixtures would remove any installation it does not list.
     */
    public function testTheLatestDirResultListsEveryInstallationOnItsServer(): void
    {
        foreach ($this->entityManager->getRepository(Server::class)->findAll() as $server) {
            $dirResult = $this->entityManager->getRepository(DetectionResult::class)->findOneBy(['server' => $server, 'type' => DetectionType::DIRECTORY], ['lastContact' => 'DESC']);
            $listed = null === $dirResult ? [] : json_decode($dirResult->getData(), true, 512, JSON_THROW_ON_ERROR);
            $installed = array_map(static fn (Installation $installation): string => $installation->getRootDir(), $server->getInstallations()->toArray());

            $this->assertEqualsCanonicalizing($listed, $installed, (string) $server->getName());
        }
    }

    /**
     * The installations and sites are written out by hand next to the
     * detection results they come from. Replaying the results must rebuild the
     * same data, or the two have drifted apart.
     */
    public function testReplayingTheDetectionResultsRebuildsTheSameData(): void
    {
        $before = $this->snapshot();

        $repository = $this->entityManager->getRepository(DetectionResult::class);
        $ids = static fn (array $results): array => array_map(static fn (DetectionResult $result) => $result->getId(), $results);
        // Like a harvest: every result, then the dir results again, which
        // fold a site's sub directory back into its installation.
        foreach ([...$ids($repository->findBy([], ['id' => 'ASC'])), ...$ids($repository->findBy(['type' => DetectionType::DIRECTORY]))] as $id) {
            self::getContainer()->get(ProcessDetectionResultHandler::class)(new ProcessDetectionResult($id));
        }
        $this->entityManager->clear();

        $this->assertSame($before, $this->snapshot());
    }

    public function testARepoIsCheckedOutOnSeveralInstallationsWithOverlappingAdvisories(): void
    {
        $repos = array_filter(
            $this->entityManager->getRepository(GitRepo::class)->findAll(),
            fn (GitRepo $repo): bool => count($this->installationsOf($repo)) > 1,
        );
        $this->assertNotEmpty($repos);

        $installations = $this->installationsOf(reset($repos));
        $tags = array_unique(array_map(static fn (Installation $installation): string => (string) $installation->getGitTag()?->getTag(), $installations));
        $this->assertGreaterThan(1, count($tags), 'Checkouts at different versions');

        [$first, $second] = array_map($this->advisoryIdsOf(...), $installations);
        $this->assertNotEmpty(array_intersect($first, $second), 'Advisories shared by the checkouts');
        $this->assertNotEmpty(array_diff($first, $second) ?: array_diff($second, $first), 'Advisories only one checkout has');
    }

    public function testARepoHasNoAdvisories(): void
    {
        $this->assertNotEmpty(array_filter(
            $this->entityManager->getRepository(GitRepo::class)->findAll(),
            fn (GitRepo $repo): bool => [] === array_merge(...array_map($this->advisoryIdsOf(...), $this->installationsOf($repo))),
        ));
    }

    public function testAnInstallationWithoutRepoHasAdvisories(): void
    {
        $this->assertNotEmpty(array_filter(
            $this->entityManager->getRepository(Installation::class)->findBy(['gitTag' => null]),
            fn (Installation $installation): bool => [] !== $this->advisoryIdsOf($installation),
        ));
    }

    public function testAdvisoriesCoverComposerAndDrupalSourcesWithAndWithoutCve(): void
    {
        $advisories = $this->entityManager->getRepository(Advisory::class)->findAll();

        $prefixes = array_unique(array_map(static fn (Advisory $advisory): string => explode('-', $advisory->getAdvisoryId())[0].'-'.explode('-', $advisory->getAdvisoryId())[1], $advisories));
        $this->assertContains('SA-CORE', $prefixes);
        $this->assertContains('SA-CONTRIB', $prefixes);
        $this->assertNotEmpty(array_filter($advisories, static fn (Advisory $advisory): bool => str_starts_with($advisory->getAdvisoryId(), 'PKSA-')));

        $this->assertNotEmpty(array_filter($advisories, static fn (Advisory $advisory): bool => null === $advisory->getCve()), 'An advisory without a CVE');

        $cves = array_filter(array_map(static fn (Advisory $advisory): ?string => $advisory->getCve(), $advisories));
        $this->assertNotSame(array_unique($cves), $cves, 'Two advisories sharing one CVE');
    }

    public function testADrupalModuleReachesAnAdvisoryThroughItsComposerPackage(): void
    {
        $this->assertNotEmpty(array_filter(
            $this->entityManager->getRepository(ModuleVersion::class)->findAll(),
            static fn (ModuleVersion $moduleVersion): bool => ($moduleVersion->getComposerPackageVersion()?->getAdvisoryCount() ?? 0) > 0,
        ));
    }

    /**
     * Fixtures are committed and loaded on every dev setup, so they must never
     * carry production data: example domains, emails and organizations,
     * private or documentation IP addresses and made-up ids only.
     */
    public function testFixturesUseExampleDataOnly(): void
    {
        $exampleHost = '/(^|\.)example\.com$/';
        foreach ($this->entityManager->getRepository(Domain::class)->findAll() as $domain) {
            $this->assertMatchesRegularExpression($exampleHost, $domain->getAddress());
        }
        foreach ($this->entityManager->getRepository(Server::class)->findAll() as $server) {
            $this->assertMatchesRegularExpression($exampleHost, (string) $server->getName());
            // RFC 1918 private and RFC 5737 documentation ranges.
            $this->assertMatchesRegularExpression('/^(10\.|192\.168\.|192\.0\.2\.|198\.51\.100\.|203\.0\.113\.)/', (string) $server->getInternalIp());
            $this->assertMatchesRegularExpression('/^(192\.0\.2\.|198\.51\.100\.|203\.0\.113\.)/', (string) $server->getExternalIp());
        }
        foreach ($this->entityManager->getRepository(OIDC::class)->findAll() as $oidc) {
            $this->assertMatchesRegularExpression($exampleHost, (string) $oidc->getDomain());
        }
        foreach ($this->entityManager->getRepository(ServiceCertificate::class)->findAll() as $certificate) {
            $this->assertMatchesRegularExpression($exampleHost, (string) $certificate->getDomain());
        }
        $emails = [
            ...array_map(static fn (User $user): string => (string) $user->getEmail(), $this->entityManager->getRepository(User::class)->findAll()),
            ...array_map(static fn (CodeOwner $owner): string => $owner->getEmail(), $this->entityManager->getRepository(CodeOwner::class)->findAll()),
            ...array_filter(array_map(static fn (SecurityContract $contract): ?string => $contract->getClientContactEmail(), $this->entityManager->getRepository(SecurityContract::class)->findAll())),
        ];
        foreach ($emails as $email) {
            $this->assertMatchesRegularExpression('/@example\.com$/', $email);
        }
        foreach ($this->entityManager->getRepository(GitRepo::class)->findAll() as $repo) {
            $this->assertSame('example-org', $repo->getOrganization());
        }
        foreach ($this->entityManager->getRepository(DockerImage::class)->findAll() as $image) {
            // Official images, stored without a repository, are public; the
            // rest belong to the example organization.
            if ('' !== $image->getRepository()) {
                $this->assertSame('example-org', $image->getOrganization(), (string) $image);
            }
        }
        foreach ($this->entityManager->getRepository(Advisory::class)->findAll() as $advisory) {
            $this->assertMatchesRegularExpression('/^(CVE-2099-\d+)?$/', (string) $advisory->getCve(), 'Made-up CVE ids only');
        }
    }

    /**
     * What the handlers derive from the detection results, keyed by root dir.
     *
     * @return array<string, mixed>
     */
    private function snapshot(): array
    {
        $names = static fn (iterable $items): array => array_values(array_unique(array_map(strval(...), [...$items])));
        $sorted = static function (array $values): array {
            sort($values);

            return $values;
        };

        $installations = [];
        foreach ($this->entityManager->getRepository(Installation::class)->findAll() as $installation) {
            $installations[$installation->getServer()->getName().$installation->getRootDir()] = [
                'type' => $installation->getType(),
                'frameworkVersion' => $installation->getFrameworkVersion(),
                'gitTag' => (string) $installation->getGitTag(),
                'packageVersions' => $sorted($names($installation->getPackageVersions())),
                'moduleVersions' => $sorted($names($installation->getModuleVersions())),
                'dockerImageTags' => $sorted($names($installation->getDockerImageTags())),
                'advisories' => $sorted($this->advisoryIdsOf($installation)),
            ];
        }
        $sites = [];
        foreach ($this->entityManager->getRepository(Site::class)->findAll() as $site) {
            $sites[$site->getServer()->getName().$site->getRootDir()] = [
                'type' => $site->getType(),
                'phpVersion' => $site->getPhpVersion(),
                'installation' => $site->getInstallation()->getRootDir(),
                'domains' => $sorted($names(array_map(static fn (Domain $domain): string => $domain->getAddress(), $site->getDomains()->toArray()))),
            ];
        }
        ksort($installations);
        ksort($sites);

        return ['installations' => $installations, 'sites' => $sites];
    }

    /**
     * @return list<Installation>
     */
    private function installationsOf(GitRepo $repo): array
    {
        $installations = [];
        foreach ($repo->getGitTags() as $gitTag) {
            array_push($installations, ...$gitTag->getInstallations());
        }

        return $installations;
    }

    /**
     * @return list<string>
     */
    private function advisoryIdsOf(Installation $installation): array
    {
        $ids = [];
        foreach ($installation->getPackageVersions() as $packageVersion) {
            foreach ($packageVersion->getAdvisories() as $advisory) {
                $ids[] = $advisory->getAdvisoryId();
            }
        }

        return $ids;
    }
}
