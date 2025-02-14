<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Advisory;
use App\Entity\Installation;
use App\Entity\Package;
use App\Entity\PackageVersion;
use App\Repository\AdvisoryRepository;
use Composer\Semver\Semver;
use Doctrine\ORM\EntityManagerInterface;

class AdvisoryFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly AdvisoryRepository     $advisoryRepository,
    )
    {
    }

    public function setAdvisories(Installation $installation, object $audit): void
    {
        if (isset($audit->advisories) && is_object($audit->advisories)) {
            foreach ($audit->advisories as $package => $advisories) {
                foreach ($advisories as $advisory) {
                    $entity = $this->advisoryRepository->findOneBy(['advisoryId' => $advisory->advisoryId]);
                    $packageVersion = $this->getInstalledPackageVersion($installation, $advisory->packageName);

                    if (null === $entity) {
                        $entity = new Advisory();
                        $this->entityManager->persist($entity);

                        $entity->addPackageVersion($packageVersion);

                        if (isset($advisory->advisoryId)) {
                            $entity->setAdvisoryId($advisory->advisoryId);
                        }
                        if (isset($advisory->affectedVersions)) {
                            $entity->setAffectedVersions($advisory->affectedVersions);
                        }
                        if (isset($advisory->title)) {
                            $entity->setTitle($advisory->title);
                        }
                        if (isset($advisory->title)) {
                            $entity->setTitle($advisory->title);
                        }
                        if (isset($advisory->cve)) {
                            $entity->setCve($advisory->cve);
                        }
                        if (isset($advisory->link)) {
                            $entity->setLink($advisory->link);
                        }
                        if (isset($advisory->reportedAt)) {
                            try {
                                $reportedAt = new \DateTimeImmutable($advisory->reportedAt);
                            } catch (\Exception) {
                                $reportedAt = new \DateTimeImmutable('now');
                            }
                            $entity->setReportedAt($reportedAt);
                        }
                        if (!empty($advisory->sources)) {
                            $entity->setSources($advisory->sources);
                        }
                    }

                    $this->setAdvisoryForAffectedVersions($packageVersion->getPackage(), $entity);
                }
            }
        }
    }

    private function getInstalledPackageVersion(Installation $installation, string $vendorPackage): PackageVersion
    {
        [$vendor, $name] = explode('/', $vendorPackage);

        /** @var PackageVersion $packageVersion */
        foreach ($installation->getPackageVersions() as $packageVersion) {
            $package = $packageVersion->getPackage();
            if ($vendor === $package->getVendor() && $name === $package->getName()) {
                return $packageVersion;
            }
        }

        throw new \InvalidArgumentException(sprintf('Installation "%s", Package "%s" not found', $installation, $vendorPackage));
    }

    private function setAdvisoryForAffectedVersions(Package $package, Advisory $advisory): void
    {
        foreach ($package->getPackageVersions() as $packageVersion) {
            if (Semver::satisfies($packageVersion->getVersion(), $advisory->getAffectedVersions())) {
                $advisory->addPackageVersion($packageVersion);
            }
        }
    }
}
