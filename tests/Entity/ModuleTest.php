<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Advisory;
use App\Entity\Module;
use App\Entity\Package;
use PHPUnit\Framework\TestCase;

class ModuleTest extends TestCase
{
    public function testUnlinkedModuleHasNoAdvisories(): void
    {
        $module = new Module();

        $this->assertNull($module->getComposerPackage());
        $this->assertCount(0, $module->getAdvisories());
        $this->assertSame(0, $module->getAdvisoryCount());
    }

    public function testLinkedModuleDelegatesAdvisoriesToPackage(): void
    {
        $package = new Package();
        $advisory = new Advisory();
        $package->addAdvisory($advisory);

        $module = new Module();
        $module->setComposerPackage($package);

        $this->assertSame($package, $module->getComposerPackage());
        $this->assertSame($package->getAdvisories(), $module->getAdvisories());
        $this->assertSame(1, $module->getAdvisoryCount());
    }

    public function testPackageInverseModules(): void
    {
        $package = new Package();
        $module = new Module();

        $package->addModule($module);
        $package->addModule($module);

        $this->assertCount(1, $package->getModules());
        $this->assertSame($package, $module->getComposerPackage());

        $package->removeModule($module);

        $this->assertCount(0, $package->getModules());
        $this->assertNull($module->getComposerPackage());
    }
}
