<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Installation;
use App\Entity\PackageVersion;
use App\Service\ModuleVersionFactory;
use App\Service\PackageVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factories against the test database.
 */
class PackageVersionFactoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EntityManagerInterface $entityManager;
    private Installation $installation;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->installation = $this->entityManager->getRepository(Installation::class)->findOneBy([]);
    }

    public function testModulesReplaceStalePackageVersion(): void
    {
        $this->packages(['drupal/fallback_probe' => '6.2.9']);
        $this->modules(['fallback_probe' => '6.2.12']);

        $this->factory()->setPackageVersionsFromModules($this->installation);

        $this->assertSame(['6.2.12'], $this->installedVersions('drupal/fallback_probe'));
    }

    public function testModulesKeepPackagesWithoutAModule(): void
    {
        $this->packages(['drupal/fallback_probe' => '6.2.9', 'acme/fallback_probe' => '1.0.0']);
        $this->modules(['fallback_probe' => '6.2.12']);

        $this->factory()->setPackageVersionsFromModules($this->installation);

        $this->assertSame(['1.0.0'], $this->installedVersions('acme/fallback_probe'));
    }

    public function testModulesWithoutAPackageAddNothing(): void
    {
        $this->packages(['drupal/fallback_probe' => '6.2.9']);
        $this->modules(['fallback_probe' => '6.2.9', 'unpackaged_probe' => '1.0.0']);
        $before = $this->installation->getPackageVersions()->count();

        $this->factory()->setPackageVersionsFromModules($this->installation);

        $this->assertCount($before, $this->installation->getPackageVersions());
    }

    public function testLegacyModuleVersionIsMapped(): void
    {
        $this->packages(['drupal/fallback_probe' => '1.18.0']);
        $this->modules(['fallback_probe' => '8.x-1.19']);

        $this->factory()->setPackageVersionsFromModules($this->installation);

        $this->assertSame(['1.19.0'], $this->installedVersions('drupal/fallback_probe'));
    }

    private function factory(): PackageVersionFactory
    {
        return self::getContainer()->get(PackageVersionFactory::class);
    }

    /**
     * @param array<string, string> $versions
     */
    private function modules(array $versions): void
    {
        $modules = [];
        foreach ($versions as $name => $version) {
            $modules[$name] = (object) ['package' => 'Probe', 'status' => 'Enabled', 'version' => $version];
        }

        self::getContainer()->get(ModuleVersionFactory::class)->setModuleVersions($this->installation, (object) $modules);
    }

    /**
     * @param array<string, string> $versions
     */
    private function packages(array $versions): void
    {
        $packages = [];
        foreach ($versions as $name => $version) {
            $packages[] = (object) ['name' => $name, 'description' => 'Probe', 'version' => $version];
        }

        $this->factory()->setPackageVersions($this->installation, $packages);
    }

    /**
     * @return list<string>
     */
    private function installedVersions(string $vendorPackage): array
    {
        $versions = [];
        /** @var PackageVersion $packageVersion */
        foreach ($this->installation->getPackageVersions() as $packageVersion) {
            $package = $packageVersion->getPackage();
            if ($vendorPackage === $package->getVendor().'/'.$package->getName()) {
                $versions[] = $packageVersion->getVersion();
            }
        }

        return $versions;
    }
}
