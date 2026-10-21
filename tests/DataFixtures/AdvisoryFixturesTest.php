<?php

declare(strict_types=1);

namespace App\Tests\DataFixtures;

use App\Entity\Advisory;
use App\Entity\Domain;
use App\Entity\GitRepo;
use App\Entity\Installation;
use App\Entity\ModuleVersion;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The fixtures model the advisory chain as it looks in production: Site →
 * Installation → PackageVersion → Advisory, with git repos and Drupal modules
 * on the side. These tests pin the cases the admin has to handle, so a fixture
 * change cannot quietly drop one.
 */
class AdvisoryFixturesTest extends KernelTestCase
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
     * carry production data: example domains and an example organization only.
     */
    public function testFixturesUseExampleDataOnly(): void
    {
        foreach ($this->entityManager->getRepository(Domain::class)->findAll() as $domain) {
            $this->assertMatchesRegularExpression('/(^|\.)example\.com$/', $domain->getAddress());
        }
        foreach ($this->entityManager->getRepository(GitRepo::class)->findAll() as $repo) {
            $this->assertSame('example-org', $repo->getOrganization());
        }
        foreach ($this->entityManager->getRepository(Advisory::class)->findAll() as $advisory) {
            $this->assertMatchesRegularExpression('/^(CVE-2099-\d+)?$/', (string) $advisory->getCve(), 'Made-up CVE ids only');
        }
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
