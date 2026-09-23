<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\PackageVersion;
use App\Service\DrupalPackageLinker;
use App\Service\ModuleVersionFactory;
use App\Service\PackageVersionFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factories against the test database, in both arrival orders.
 */
class DrupalPackageLinkerTest extends KernelTestCase
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

    public function testLinksWhenModuleArrivesFirst(): void
    {
        $this->modules('linker_probe', '2.2.0');
        $this->packages('drupal/linker_probe', '2.2.0');

        $this->assertLinked('linker_probe', '2.2.0', '2.2.0');
    }

    public function testLinksWhenPackageArrivesFirst(): void
    {
        $this->packages('drupal/linker_probe', '2.2.0');
        $this->modules('linker_probe', '2.2.0');

        $this->assertLinked('linker_probe', '2.2.0', '2.2.0');
    }

    public function testLinksLegacyVersions(): void
    {
        $this->modules('linker_probe', '8.x-1.19');
        $this->packages('drupal/linker_probe', '1.19.0');

        $this->assertLinked('linker_probe', '8.x-1.19', '1.19.0');
    }

    public function testSecondCallChangesNothing(): void
    {
        $this->packages('drupal/linker_probe', '2.2.0');
        $this->modules('linker_probe', '2.2.0');

        $linker = self::getContainer()->get(DrupalPackageLinker::class);
        $moduleVersion = $this->moduleVersion('linker_probe', '2.2.0');

        $this->assertFalse($linker->linkModuleVersion($moduleVersion));
        $this->assertFalse($linker->linkPackageVersion($moduleVersion->getComposerPackageVersion()));
    }

    public function testEmptyModuleVersionLinksOnlyTheModule(): void
    {
        $this->packages('drupal/linker_probe', '2.2.0');
        $this->modules('linker_probe', '');

        $moduleVersion = $this->moduleVersion('linker_probe', '');
        $this->assertNull($moduleVersion->getComposerPackageVersion());
        $this->assertSame('linker_probe', $moduleVersion->getModule()->getComposerPackage()?->getName());
    }

    public function testUnmatchedVersionLinksOnlyTheModule(): void
    {
        $this->packages('drupal/linker_probe', '2.2.0');
        $this->modules('linker_probe', '2.2.4');

        $moduleVersion = $this->moduleVersion('linker_probe', '2.2.4');
        $this->assertNull($moduleVersion->getComposerPackageVersion());
        $this->assertSame('linker_probe', $moduleVersion->getModule()->getComposerPackage()?->getName());
    }

    public function testNonDrupalPackageIsIgnored(): void
    {
        $this->modules('linker_probe', '2.2.0');
        $this->packages('acme/linker_probe', '2.2.0');

        $this->assertNull($this->moduleVersion('linker_probe', '2.2.0')->getComposerPackageVersion());
    }

    private function modules(string $name, string $version): void
    {
        self::getContainer()->get(ModuleVersionFactory::class)->setModuleVersions($this->installation, (object) [
            $name => (object) ['package' => 'Security', 'status' => 'Enabled', 'version' => $version],
        ]);
    }

    private function packages(string $name, string $version): void
    {
        self::getContainer()->get(PackageVersionFactory::class)->setPackageVersions($this->installation, [
            (object) ['name' => $name, 'description' => 'Probe', 'version' => $version],
        ]);
    }

    private function moduleVersion(string $name, string $version): ModuleVersion
    {
        $module = $this->entityManager->getRepository(Module::class)->findOneBy(['name' => $name]);

        return $this->entityManager->getRepository(ModuleVersion::class)->findOneBy(['module' => $module, 'version' => $version]);
    }

    private function assertLinked(string $name, string $moduleVersion, string $packageVersion): void
    {
        $this->entityManager->clear();

        $linked = $this->moduleVersion($name, $moduleVersion)->getComposerPackageVersion();
        $this->assertInstanceOf(PackageVersion::class, $linked);
        $this->assertSame($packageVersion, $linked->getVersion());
        $this->assertSame('drupal', $linked->getPackage()->getVendor());
        $this->assertSame($name, $linked->getPackage()->getName());
        $this->assertSame($linked->getPackage(), $this->moduleVersion($name, $moduleVersion)->getModule()->getComposerPackage());
    }
}
