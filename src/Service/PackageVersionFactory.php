<?php

declare(strict_types=1);

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
        // Entities are persisted below but not flushed until the end of this
        // method, so the repositories cannot find them yet. These two maps
        // stand in for the repositories for the rest of the call, keeping the
        // same package from being created twice.
        //
        // They are locals, not properties. Nothing survives the method, so the
        // service holds no state between calls — which is what makes it safe in
        // a long-running process, the messenger consumer included.
        $createdPackages = [];
        $createdPackageVersions = [];

        $packageVersions = new ArrayCollection();
        foreach ($installedPackages as $installed) {
            [$vendor, $name] = explode('/', (string) $installed->name);

            $package = $this->getPackage($vendor, $name, $createdPackages);

            $package->setDescription($installed->description);
            if (isset($installed->warning)) {
                $package->setWarning($installed->warning);
            }
            if (isset($installed->abandoned)) {
                $package->setAbandoned($installed->abandoned);
            }

            $packageVersion = $this->getPackageVersion($package, $installed->version, $createdPackageVersions);
            $installation->addPackageVersion($packageVersion);

            $packageVersion->setVersion($installed->version);
            if (isset($installed->latest)) {
                $packageVersion->setLatest($installed->latest);
            }
            if (isset($installed->{'latest-status'})) {
                $packageVersion->setLatestStatus($installed->{'latest-status'});
            }

            $packageVersions->add($packageVersion);
        }

        $installation->setPackageVersions($packageVersions);

        $this->entityManager->flush();
    }

    /**
     * @param array<string, Package> $createdPackages packages persisted in this call but not yet flushed, keyed by vendor and name
     */
    private function getPackage(string $vendor, string $name, array &$createdPackages): Package
    {
        $key = $vendor."\0".$name;

        $package = $this->packageRepository->findOneBy([
            'vendor' => $vendor,
            'name' => $name,
        ]) ?? $createdPackages[$key] ?? null;

        if (null === $package) {
            $package = new Package();
            $this->entityManager->persist($package);

            $package->setVendor($vendor);
            $package->setName($name);

            $createdPackages[$key] = $package;
        }

        return $package;
    }

    /**
     * @param array<string, PackageVersion> $createdPackageVersions versions persisted in this call but not yet flushed, keyed by package identity and version
     */
    private function getPackageVersion(Package $package, string $version, array &$createdPackageVersions): PackageVersion
    {
        // Keyed on object identity rather than on the id: within one call the
        // package may have been created moments ago, and object identity holds
        // whether or not Doctrine has assigned an id yet.
        $key = spl_object_id($package)."\0".$version;

        $packageVersion = $this->packageVersionRepository->findOneBy([
            'package' => $package,
            'version' => $version,
        ]) ?? $createdPackageVersions[$key] ?? null;

        if (null === $packageVersion) {
            $packageVersion = new PackageVersion();
            $this->entityManager->persist($packageVersion);

            $package->addPackageVersion($packageVersion);
            $packageVersion->setVersion($version);

            $createdPackageVersions[$key] = $packageVersion;
        }

        return $packageVersion;
    }
}
