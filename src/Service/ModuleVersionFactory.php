<?php

namespace App\Service;

use App\Entity\Installation;
use App\Entity\Module;
use App\Entity\ModuleVersion;
use App\Entity\Package;
use App\Entity\PackageVersion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class ModuleVersionFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function setModuleVersions(Installation $installation, object $installedModules): void
    {
        $moduleRepository = $this->entityManager->getRepository(Module::class);
        $moduleVersionRepository = $this->entityManager->getRepository(ModuleVersion::class);

        $moduleVersions = new ArrayCollection();
        foreach ($installedModules as $name => $installed) {
            $module = $moduleRepository->findOneBy([
                'name' => $name,
                'package' => $installed->package,
            ]);

            if (null === $module) {
                $module = new Module();
                $module->setPackage($installed->package);
                $module->setName($name);
                if (isset($installed->display_name)) {
                    $module->setDisplayName($installed->display_name);
                }
                $module->setEnabled($installed->status === 'Enabled');

                $this->entityManager->persist($module);
                $this->entityManager->flush();
            }

            $moduleVersion = $moduleVersionRepository->findOneBy([
                'module' => $module,
                'version' => $installed->version,
            ]);

            if (null === $moduleVersion) {
                $moduleVersion = new ModuleVersion();

                $module->addModuleVersion($moduleVersion);
                $installation->addModuleVersion($moduleVersion);

                $this->entityManager->persist($moduleVersion);
            }

            $moduleVersion->setVersion($installed->version);

            $moduleVersions->add($moduleVersion);
        }

        $installation->setModuleVersions($moduleVersions);

        $this->entityManager->flush();
    }
}
