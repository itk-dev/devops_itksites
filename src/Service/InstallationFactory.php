<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Repository\InstallationRepository;
use App\Utils\RootDirNormalizer;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

class InstallationFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InstallationRepository $repository,
    ) {
    }

    /**
     * Get Installations from detection result.
     *
     * @param DetectionResult $detectionResult
     *
     * @return Collection<int, Installation>
     */
    public function getInstallations(DetectionResult $detectionResult): Collection
    {
        try {
            $rootDirs = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $installations = new ArrayCollection();
            foreach ($rootDirs as $rootDir) {
                $installations->add($this->getInstallation($detectionResult, $rootDir));
            }

            $this->entityManager->flush();

            return $installations;
        } catch (\JsonException) {
            // @TODO log exceptions

            return new ArrayCollection();
        }
    }

    public function getInstallation(DetectionResult $detectionResult, ?string $rootDir = null): Installation
    {
        $rootDir ??= $detectionResult->getRootDir();
        $rootDir = RootDirNormalizer::normalize($rootDir);

        $installation = $this->repository->findOneBy([
            'rootDir' => $rootDir,
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $installation) {
            $installation = new Installation();
            $this->entityManager->persist($installation);
        }

        $installation->setDetectionResult($detectionResult);
        $installation->setRootDir($rootDir);

        return $installation;
    }
}
