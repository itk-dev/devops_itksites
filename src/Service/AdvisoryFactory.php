<?php

namespace App\Service;

use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\PackageRepository;
use App\Repository\PackageVersionRepository;
use Doctrine\ORM\EntityManagerInterface;

class AdvisoryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PackageRepository $packageRepository,
        private readonly PackageVersionRepository $packageVersionRepository,
    ) {
    }

    public function setAdvisories(Installation $installation, object $audit): void
    {
        if (isset($audit->advisories) && is_object($audit->advisories)) {
            foreach ($audit->advisories as $package => $advisories) {
                foreach ($advisories as $advisory) {

                }
            }
        }

    }

    private function getInstalledPackage(Installation $installation, string $vendorPackage): Package
    {
        [$vendor, $name] = explode('/', $vendorPackage);

        /** @var PackageVersion $packageVersion */
        foreach ($installation->getPackageVersions() as $packageVersion) {
            $package = $packageVersion->getPackage();
            if ($vendor === $package->getVendor() && $name === $package->getName()) {
                return $package;
            }
        }
    }
}