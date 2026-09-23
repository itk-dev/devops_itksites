<?php

declare(strict_types=1);

namespace App\Tests\Service\Drupal;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\AdvisoryRepository;
use App\Service\AdvisoryFactory;
use App\Service\Drupal\ReleaseChecker;
use App\Service\Drupal\ReleaseHistoryClient;
use App\Service\Drupal\SecurityAdvisoryClient;
use App\Service\ModuleVersionFactory;
use App\Service\PackageVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Runs the checker against the test database with drupal.org mocked by captured fixtures.
 */
class ReleaseCheckerTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private const string FIXTURES = __DIR__.'/../../fixtures/drupal/';

    private EntityManagerInterface $entityManager;
    private Installation $installation;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->installation = $this->entityManager->getRepository(Installation::class)->findOneBy([]);
    }

    public function testInsecureVersionGetsAdvisory(): void
    {
        $this->install('key_auth', ['2.2.0' => '2.2.0', '2.2.4' => '2.2.4']);

        $this->checker(
            $this->fixture('release-history-key_auth.xml'),
            $this->fixture('project-key_auth.json'),
            $this->fixture('sa-key_auth.json'),
        )->check($this->package('key_auth'));
        $this->entityManager->clear();

        $insecure = $this->packageVersion('key_auth', '2.2.0');
        $this->assertTrue($insecure->isDrupalInsecure());
        $this->assertSame(['Insecure'], $insecure->getDrupalReleaseTerms());
        $this->assertNotNull($insecure->getDrupalReleaseCheckedAt());
        $this->assertCount(1, $insecure->getAdvisories());

        $advisory = $insecure->getAdvisories()->first();
        $this->assertSame('SA-CONTRIB-2026-138', $advisory->getAdvisoryId());
        $this->assertSame('https://www.drupal.org/sa-contrib-2026-138', $advisory->getLink());
        $this->assertSame('<2.2.4', $advisory->getAffectedVersions());
        $this->assertSame('CVE-2026-87940', $advisory->getCve());
        $this->assertSame([['name' => 'Drupal', 'remoteId' => 'SA-CONTRIB-2026-138']], $advisory->getSources());
        $this->assertSame(['https://www.drupal.org/sa-contrib-2026-138'], $advisory->getSourceLinks());
        $this->assertSame('key_auth', $advisory->getPackage()?->getName());

        $this->assertSame([$advisory], $this->moduleVersion('key_auth', '2.2.0')->getAdvisories()->toArray());

        $fixed = $this->packageVersion('key_auth', '2.2.4');
        $this->assertFalse($fixed->isDrupalInsecure());
        $this->assertSame(['Security update'], $fixed->getDrupalReleaseTerms());
        $this->assertCount(0, $fixed->getAdvisories());
    }

    public function testSecondRunCreatesNoDuplicate(): void
    {
        $this->install('key_auth', ['2.2.0' => '2.2.0']);
        $checker = $this->checker(
            $this->fixture('release-history-key_auth.xml'),
            $this->fixture('project-key_auth.json'),
            $this->fixture('sa-key_auth.json'),
        );

        $checker->check($this->package('key_auth'));
        $this->entityManager->clear();
        $checker->check($this->package('key_auth'));
        $this->entityManager->clear();

        $this->assertSame(1, self::getContainer()->get(AdvisoryRepository::class)->count(['advisoryId' => 'SA-CONTRIB-2026-138']));
        $this->assertCount(1, $this->packageVersion('key_auth', '2.2.0')->getAdvisories());
    }

    public function testLegacyVersionResolves(): void
    {
        $this->install('checker_legacy', ['8.x-1.19' => '1.19.0']);
        $history = '<?xml version="1.0" encoding="utf-8"?><project><short_name>checker_legacy</short_name><releases>'
            .'<release><version>8.x-1.19</version><terms><term><name>Release type</name><value>Insecure</value></term></terms></release>'
            .'</releases></project>';
        $advisories = (string) json_encode(['list' => [[
            'title' => 'Checker legacy - Critical - SA-CONTRIB-2026-999',
            'url' => 'https://www.drupal.org/sa-contrib-2026-999',
            'field_affected_versions' => '<8.x-1.20',
            'created' => '1788974270',
        ]]]);

        $this->checker($history, $this->fixture('project-key_auth.json'), $advisories)->check($this->package('checker_legacy'));
        $this->entityManager->clear();

        $packageVersion = $this->packageVersion('checker_legacy', '1.19.0');
        $this->assertTrue($packageVersion->isDrupalInsecure());
        $this->assertSame('<1.20.0', $packageVersion->getAdvisories()->first()->getAffectedVersions());
        $this->assertCount(1, $this->moduleVersion('checker_legacy', '8.x-1.19')->getAdvisories());
    }

    public function testInsecureWithoutMatchingAdvisoryStaysFlagged(): void
    {
        $this->install('key_auth', ['2.2.0' => '2.2.0']);

        $this->checker(
            $this->fixture('release-history-key_auth.xml'),
            $this->fixture('project-key_auth.json'),
            (string) json_encode(['list' => []]),
        )->check($this->package('key_auth'));
        $this->entityManager->clear();

        $packageVersion = $this->packageVersion('key_auth', '2.2.0');
        $this->assertTrue($packageVersion->isDrupalInsecure());
        $this->assertCount(0, $packageVersion->getAdvisories());
    }

    public function testUnknownProjectAndVersion(): void
    {
        $this->install('checker_unknown', ['1.0.0' => '1.0.0']);

        $this->checker($this->fixture('release-history-error.xml'))->check($this->package('checker_unknown'));
        $this->entityManager->clear();

        $packageVersion = $this->packageVersion('checker_unknown', '1.0.0');
        $this->assertFalse($packageVersion->isDrupalInsecure());
        $this->assertNull($packageVersion->getDrupalReleaseTerms());
        $this->assertNotNull($packageVersion->getDrupalReleaseCheckedAt());
    }

    public function testNonDrupalPackageIsIgnored(): void
    {
        self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($this->installation, [
            (object) ['name' => 'acme/checker_probe', 'description' => 'Probe', 'version' => '1.0.0'],
        ]);
        $package = $this->entityManager->getRepository(Package::class)->findOneBy(['vendor' => 'acme', 'name' => 'checker_probe']);

        // No mocked responses: any request would fail the test.
        $this->checker()->check($package);

        $this->assertNull($package->getPackageVersions()->first()->getDrupalReleaseCheckedAt());
    }

    private function checker(string ...$bodies): ReleaseChecker
    {
        $responses = array_map(static fn (string $body): MockResponse => new MockResponse($body), $bodies);
        $releaseHistory = new MockHttpClient(array_slice($responses, 0, 1), 'https://updates.drupal.org/');
        $securityAdvisories = new MockHttpClient(array_slice($responses, 1), 'https://www.drupal.org/');
        $container = self::getContainer();

        return new ReleaseChecker(
            new ReleaseHistoryClient($releaseHistory, new ArrayAdapter()),
            new SecurityAdvisoryClient($securityAdvisories, new ArrayAdapter()),
            $container->get(AdvisoryFactory::class),
            $container->get(AdvisoryRepository::class),
            $this->entityManager,
        );
    }

    private function fixture(string $name): string
    {
        return (string) file_get_contents(self::FIXTURES.$name);
    }

    /**
     * @param array<string, string> $versions module version => package version
     */
    private function install(string $name, array $versions): void
    {
        // One installation per version: setting versions on an installation replaces its old ones.
        $installations = $this->entityManager->getRepository(Installation::class)->findBy([], limit: count($versions));
        foreach ($versions as $moduleVersion => $packageVersion) {
            $installation = array_shift($installations);
            self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($installation, [
                (object) ['name' => 'drupal/'.$name, 'description' => 'Probe', 'version' => $packageVersion],
            ]);
            self::getContainer()->get(ModuleVersionFactory::class)->setModuleVersions($installation, (object) [
                $name => (object) ['package' => 'Security', 'status' => 'Enabled', 'version' => $moduleVersion],
            ]);
        }
    }

    private function package(string $name): Package
    {
        return $this->entityManager->getRepository(Package::class)->findOneBy(['vendor' => 'drupal', 'name' => $name]);
    }

    private function packageVersion(string $name, string $version): PackageVersion
    {
        return $this->entityManager->getRepository(PackageVersion::class)->findOneBy(['package' => $this->package($name), 'version' => $version]);
    }

    private function moduleVersion(string $name, string $version): ModuleVersion
    {
        $module = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => $name]);

        return $this->entityManager->getRepository(ModuleVersion::class)->findOneBy(['module' => $module, 'version' => $version]);
    }
}
