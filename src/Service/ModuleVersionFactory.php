<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Repository\ModuleRepository;
use App\Repository\ModuleVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ModuleVersionFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ModuleRepository $moduleRepository,
        private readonly ModuleVersionRepository $moduleVersionRepository,
    ) {
    }

    public function setModuleVersions(Installation $installation, object $installedModules): void
    {
        // Locals, not properties: see PackageVersionFactory for why. Entities
        // persisted below are not flushed until the end of the call, so these
        // maps stand in for the repositories until then, and nothing outlives
        // the method.
        $createdModules = [];
        $createdModuleVersions = [];

        $moduleVersions = new ArrayCollection();
        foreach ($installedModules as $name => $installed) {
            $module = $this->getModule($name, $installed->package, $createdModules);

            if (isset($installed->display_name)) {
                $module->setDisplayName($installed->display_name);
            }
            $module->setEnabled('Enabled' === $installed->status);

            $moduleVersion = $this->getModuleVersion($module, $installed->version, $createdModuleVersions);
            $moduleVersions->add($moduleVersion);
        }

        $installation->setModuleVersions($moduleVersions);

        $this->entityManager->flush();
    }

    /**
     * @param array<string, Module> $createdModules modules persisted in this call but not yet flushed, keyed by name and package
     */
    private function getModule(string $name, string $package, array &$createdModules): Module
    {
        $key = $name."\0".$package;

        $module = $this->moduleRepository->findOneBy([
            'name' => $name,
            'package' => $package,
        ]) ?? $createdModules[$key] ?? null;

        if (null === $module) {
            $module = new Module();
            $this->entityManager->persist($module);

            $module->setName($name);
            $module->setPackage($package);

            $createdModules[$key] = $module;
        }

        return $module;
    }

    /**
     * @param array<string, ModuleVersion> $createdModuleVersions versions persisted in this call but not yet flushed, keyed by module identity and version
     */
    private function getModuleVersion(Module $module, string|int|float|null $version, array &$createdModuleVersions): ModuleVersion
    {
        if (is_int($version) || is_float($version)) {
            $version = (string) $version;
        }

        $moduleVersion = $this->moduleVersionRepository->findOneBy([
            'module' => $module,
            'version' => $version,
        ]);

        // A null version is deliberately left out of the buffer.
        // ModuleVersion::getVersion() reports 'Unknown' for null, so the scan
        // this replaced never matched a null-versioned module either. Keeping
        // that quirk keeps this change about state and nothing else; see the
        // note in the pull request.
        $key = null === $version ? null : spl_object_id($module)."\0".$version;

        if (null === $moduleVersion && null !== $key) {
            $moduleVersion = $createdModuleVersions[$key] ?? null;
        }

        if (null === $moduleVersion) {
            $moduleVersion = new ModuleVersion();
            $this->entityManager->persist($moduleVersion);

            $module->addModuleVersion($moduleVersion);
            $moduleVersion->setVersion($version);

            if (null !== $key) {
                $createdModuleVersions[$key] = $moduleVersion;
            }
        }

        return $moduleVersion;
    }
}
