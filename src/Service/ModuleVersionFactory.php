<?php

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
    private array $createdModules = [];
    private array $createdModuleVersions = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly ModuleRepository $moduleRepository,
        private readonly ModuleVersionRepository $moduleVersionRepository,
    ) {
    }

    public function setModuleVersions(Installation $installation, object $installedModules): void
    {
        $moduleVersions = new ArrayCollection();
        foreach ($installedModules as $name => $installed) {
            $module = $this->getModule($name, $installed->package);

            if (isset($installed->display_name)) {
                $module->setDisplayName($installed->display_name);
            }
            $module->setEnabled('Enabled' === $installed->status);

            $moduleVersion = $this->getModuleVersion($module, $installed->version);
            $moduleVersions->add($moduleVersion);
        }

        $installation->setModuleVersions($moduleVersions);

        $this->entityManager->flush();
        $this->createdModules = [];
        $this->createdModuleVersions = [];
    }

    private function getModule(string $name, string $package): Module
    {
        $module = $this->moduleRepository->findOneBy([
            'name' => $name,
            'package' => $package,
        ]);

        if (null === $module) {
            /** @var Module $createdModule */
            foreach ($this->createdModules as $createdModule) {
                if ($name === $createdModule->getName() && $package === $createdModule->getPackage()) {
                    $module = $createdModule;
                }
            }
        }

        if (null === $module) {
            $module = new Module();
            $this->entityManager->persist($module);

            $module->setName($name);
            $module->setPackage($package);
        }

        return $module;
    }

    private function getModuleVersion(Module $module, ?string $version): ModuleVersion
    {
        $moduleVersion = $this->moduleVersionRepository->findOneBy([
            'module' => $module,
            'version' => $version,
        ]);

        if (null === $moduleVersion) {
            /** @var ModuleVersion $createdModuleVersion */
            foreach ($this->createdModuleVersions as $createdModuleVersion) {
                if ($module->getId() === $createdModuleVersion->getModule()->getId() && $version === $createdModuleVersion->getVersion()) {
                    $moduleVersion = $createdModuleVersion;
                }
            }
        }

        if (null === $moduleVersion) {
            $moduleVersion = new ModuleVersion();
            $this->entityManager->persist($moduleVersion);

            $module->addModuleVersion($moduleVersion);
            $moduleVersion->setVersion($version);
        }

        return $moduleVersion;
    }
}
