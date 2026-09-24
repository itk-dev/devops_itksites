<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\PackageRepository;
use App\Repository\PackageVersionRepository;
use App\Utils\DrupalVersion;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class PackageVersionFactory
{
    private array $createdPackages = [];
    private array $createdPackageVersions = [];

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly PackageRepository $packageRepository,
        private readonly PackageVersionRepository $packageVersionRepository,
        private readonly DrupalPackageLinker $drupalPackageLinker,
    ) {
    }

    public function setPackageVersions(Installation $installation, array $installedPackages): void
    {
        $packageVersions = new ArrayCollection();
        foreach ($installedPackages as $installed) {
            [$vendor, $name] = explode('/', (string) $installed->name);

            $package = $this->getPackage($vendor, $name);

            $package->setDescription($installed->description);
            if (isset($installed->warning)) {
                $package->setWarning($installed->warning);
            }
            if (isset($installed->abandoned)) {
                $package->setAbandoned($installed->abandoned);
            }

            $packageVersion = $this->getPackageVersion($package, $installed->version);
            $installation->addPackageVersion($packageVersion);

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

        $this->entityManager->flush();
        $this->createdPackages = [];
        $this->createdPackageVersions = [];
    }

    /**
     * Set the installation's drupal/* package versions from its Drupal modules.
     *
     * Fallback for detection results without Composer packages. Packages with
     * no linked module keep their last known version.
     */
    public function setPackageVersionsFromModules(Installation $installation): void
    {
        foreach ($installation->getModuleVersions() as $moduleVersion) {
            $package = $moduleVersion->getModule()->getComposerPackage();
            $version = DrupalVersion::toComposer($moduleVersion->getVersion());
            if (null === $package || null === $version) {
                continue;
            }

            foreach ($installation->getPackageVersions()->toArray() as $packageVersion) {
                if ($package === $packageVersion->getPackage()) {
                    $installation->removePackageVersion($packageVersion);
                }
            }

            $installation->addPackageVersion($this->getPackageVersion($package, $version));
        }

        $this->entityManager->flush();
        $this->createdPackageVersions = [];
    }

    private function getPackage(string $vendor, string $name): Package
    {
        $package = $this->packageRepository->findOneBy([
            'vendor' => $vendor,
            'name' => $name,
        ]);

        if (null === $package) {
            /** @var Package $createdPackage */
            foreach ($this->createdPackages as $createdPackage) {
                if ($vendor === $createdPackage->getVendor() && $name === $createdPackage->getName()) {
                    $package = $createdPackage;
                }
            }
        }

        if (null === $package) {
            $package = new Package();
            $this->entityManager->persist($package);

            $package->setVendor($vendor);
            $package->setName($name);

            $this->createdPackages[] = $package;
        }

        return $package;
    }

    private function getPackageVersion(Package $package, string $version): PackageVersion
    {
        $packageVersion = $this->packageVersionRepository->findOneBy([
            'package' => $package,
            'version' => $version,
        ]);

        if (null === $packageVersion) {
            /* @var PackageVersion $packageVersion */
            foreach ($this->createdPackageVersions as $createdPackageVersion) {
                if ($package->getId() === $createdPackageVersion->getPackage()->getId() && $version === $createdPackageVersion->getVersion()) {
                    $packageVersion = $createdPackageVersion;
                }
            }
        }

        if (null === $packageVersion) {
            $packageVersion = new PackageVersion();
            $this->entityManager->persist($packageVersion);

            $package->addPackageVersion($packageVersion);
            $packageVersion->setVersion($version);
            $this->drupalPackageLinker->linkPackageVersion($packageVersion);

            $this->createdPackageVersions[] = $packageVersion;
        }

        return $packageVersion;
    }
}
