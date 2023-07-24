<?php

namespace App\Service;

use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\PackageRepository;
use App\Repository\PackageVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PackageVersionFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PackageRepository $packageRepository,
        private readonly PackageVersionRepository $packageVersionRepository,
    ) {
    }

    public function setPackageVersions(Installation $installation, array $installedPackages): void
    {
        $packageVersions = new ArrayCollection();
        foreach ($installedPackages as $installed) {
            [$vendor, $name] = explode('/', $installed->name);

            $package = $this->packageRepository->findOneBy([
                'vendor' => $vendor,
                'package' => $name,
            ]);

            if (null === $package) {
                $package = new Package();
                $this->entityManager->persist($package);

                $package->setVendor($vendor);
                $package->setName($name);
                $package->setDescription($installed->description);
                if (isset($installed->warning)) {
                    $package->setWarning($installed->warning);
                }
                if (isset($installed->abandoned)) {
                    $package->isAbandoned($installed->abandoned);
                }
            }

            $packageVersion = $this->packageVersionRepository->findOneBy([
                'package' => $package,
                'version' => $installed->version,
            ]);

            if (null === $packageVersion) {
                $packageVersion = new PackageVersion();
                $this->entityManager->persist($packageVersion);

                $package->addPackageVersion($packageVersion);
                $installation->addPackageVersion($packageVersion);
            }

            $packageVersion->setVersion($installed->version);
            if (isset($installed->latest)) {
                $packageVersion->setLatest($installed->latest);
            }
            if (isset($installed->{'latest-status'})) {
                $packageVersion->setLatestStatus($installed->{'latest-status'});
            }
            if (isset($installed->{'latest-status'})) {
                $packageVersion->setLatestStatus($installed->{'latest-status'});
            }

            $packageVersions->add($packageVersion);
        }

        $installation->setPackageVersions($packageVersions);
    }
}
