<?php

declare(strict_types=1);

namespace App\Tests\Entity;

use App\Entity\Advisory;
use App\Entity\ModuleVersion;
use App\Entity\PackageVersion;
use PHPUnit\Framework\TestCase;

class ModuleVersionTest extends TestCase
{
    public function testUnlinkedModuleVersionHasNoAdvisories(): void
    {
        $moduleVersion = new ModuleVersion();

        $this->assertNull($moduleVersion->getComposerPackageVersion());
        $this->assertCount(0, $moduleVersion->getAdvisories());
        $this->assertSame(0, $moduleVersion->getAdvisoryCount());
    }

    public function testLinkedModuleVersionDelegatesAdvisoriesToPackageVersion(): void
    {
        $packageVersion = new PackageVersion();
        $packageVersion->addAdvisory(new Advisory());
        $packageVersion->addAdvisory(new Advisory());

        $moduleVersion = new ModuleVersion();
        $moduleVersion->setComposerPackageVersion($packageVersion);

        $this->assertSame($packageVersion, $moduleVersion->getComposerPackageVersion());
        $this->assertSame($packageVersion->getAdvisories(), $moduleVersion->getAdvisories());
        $this->assertSame(2, $moduleVersion->getAdvisoryCount());
    }

    public function testPackageVersionInverseModuleVersions(): void
    {
        $packageVersion = new PackageVersion();
        $moduleVersion = new ModuleVersion();

        $packageVersion->addModuleVersion($moduleVersion);
        $packageVersion->addModuleVersion($moduleVersion);

        $this->assertCount(1, $packageVersion->getModuleVersions());
        $this->assertSame($packageVersion, $moduleVersion->getComposerPackageVersion());

        $packageVersion->removeModuleVersion($moduleVersion);

        $this->assertCount(0, $packageVersion->getModuleVersions());
        $this->assertNull($moduleVersion->getComposerPackageVersion());
    }
}
