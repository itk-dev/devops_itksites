<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class DrupalLinkPackagesCommandTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    public function testBackfillsExistingRows(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);

        // Rows written without the factories, as before the linker existed.
        $package = new Package()->setVendor('drupal')->setName('backfill_probe');
        $packageVersion = new PackageVersion()->setVersion('1.19.0');
        $package->addPackageVersion($packageVersion);
        $module = new Module()->setName('backfill_probe')->setPackage('Other')->setEnabled(true);
        $moduleVersion = new ModuleVersion()->setVersion('8.x-1.19');
        $module->addModuleVersion($moduleVersion);
        foreach ([$package, $packageVersion, $module, $moduleVersion] as $entity) {
            $entityManager->persist($entity);
        }
        // Rows no installation uses are deleted on flush.
        $entityManager->getRepository(Installation::class)->findOneBy([])
            ->addPackageVersion($packageVersion)
            ->addModuleVersion($moduleVersion);
        $entityManager->flush();

        $tester = new CommandTester(new Application(self::$kernel)->find('app:drupal:link-packages'));

        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Linked 1 modules and 1 module versions.', $tester->getDisplay());
        $this->assertSame($packageVersion, $moduleVersion->getComposerPackageVersion());
        $this->assertSame($package, $module->getComposerPackage());

        $tester->execute([]);
        $tester->assertCommandIsSuccessful();
        $this->assertStringContainsString('Linked 1 modules and 1 module versions.', $tester->getDisplay());
        $this->assertSame($packageVersion, $moduleVersion->getComposerPackageVersion());
    }
}
