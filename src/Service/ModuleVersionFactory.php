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
            $module = $this->moduleRepository->findOneBy([
                'name' => $name,
                'package' => $installed->package,
            ]);

            if (null === $module) {
                $module = new Module();
                $this->entityManager->persist($module);

                $module->setPackage($installed->package);
                $module->setName($name);
                if (isset($installed->display_name)) {
                    $module->setDisplayName($installed->display_name);
                }
                $module->setEnabled('Enabled' === $installed->status);
            }

            $moduleVersion = $this->moduleVersionRepository->findOneBy([
                'module' => $module,
                'version' => $installed->version,
            ]);

            if (null === $moduleVersion) {
                $moduleVersion = new ModuleVersion();
                $this->entityManager->persist($moduleVersion);

                $module->addModuleVersion($moduleVersion);
                $installation->addModuleVersion($moduleVersion);
            }

            $moduleVersion->setVersion($installed->version);

            $moduleVersions->add($moduleVersion);
        }

        $installation->setModuleVersions($moduleVersions);
    }
}
