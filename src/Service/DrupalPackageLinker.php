<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\ModuleRepository;
use App\Repository\PackageRepository;
use App\Repository\PackageVersionRepository;
use App\Utils\DrupalVersion;

/**
 * Links Drupal modules to the drupal/<module name> Composer packages they ship as.
 *
 * Either side may arrive first, so both factories call this when they create a
 * row, and whichever arrives second completes the link. Linking is idempotent.
 */
class DrupalPackageLinker
{
    private const string VENDOR = 'drupal';

    public function __construct(
        private readonly ModuleRepository $moduleRepository,
        private readonly PackageRepository $packageRepository,
        private readonly PackageVersionRepository $packageVersionRepository,
    ) {
    }

    /**
     * Link a module version, and its module, to the matching package side.
     *
     * @return bool true if anything changed
     */
    public function linkModuleVersion(ModuleVersion $moduleVersion): bool
    {
        $module = $moduleVersion->getModule();
        $package = $this->packageRepository->findOneBy(['vendor' => self::VENDOR, 'name' => $module->getName()]);
        if (null === $package) {
            return false;
        }

        $changed = $this->linkModule($module, $package);

        $version = DrupalVersion::toComposer($moduleVersion->getVersion());
        if (null === $version) {
            return $changed;
        }

        $packageVersion = $this->packageVersionRepository->findOneBy(['package' => $package, 'version' => $version]);
        if (null === $packageVersion) {
            return $changed;
        }

        return $this->link($moduleVersion, $packageVersion) || $changed;
    }

    /**
     * Link a drupal/* package version, and its package, to the matching modules.
     *
     * @return bool true if anything changed
     */
    public function linkPackageVersion(PackageVersion $packageVersion): bool
    {
        $package = $packageVersion->getPackage();
        if (self::VENDOR !== $package->getVendor()) {
            return false;
        }

        $changed = false;
        foreach ($this->moduleRepository->findBy(['name' => $package->getName()]) as $module) {
            $changed = $this->linkModule($module, $package) || $changed;

            foreach ($module->getModuleVersions() as $moduleVersion) {
                if (DrupalVersion::toComposer($moduleVersion->getVersion()) === $packageVersion->getVersion()) {
                    $changed = $this->link($moduleVersion, $packageVersion) || $changed;
                }
            }
        }

        return $changed;
    }

    private function linkModule(Module $module, Package $package): bool
    {
        if ($module->getComposerPackage() === $package) {
            return false;
        }

        $package->addModule($module);

        return true;
    }

    private function link(ModuleVersion $moduleVersion, PackageVersion $packageVersion): bool
    {
        if ($moduleVersion->getComposerPackageVersion() === $packageVersion) {
            return false;
        }

        $packageVersion->addModuleVersion($moduleVersion);

        return true;
    }
}
