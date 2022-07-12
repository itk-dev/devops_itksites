<?php

namespace App\Service;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Repository\InstallationRepository;
use Doctrine\ORM\EntityManagerInterface;

class InstallationFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InstallationRepository $repository
    ) {
    }

    public function getInstallation(DetectionResult $detectionResult): Installation
    {
        $installation = $this->repository->findOneBy([
            'rootDir' => $detectionResult->getRootDir(),
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $installation) {
            $installation = new Installation();
            $this->entityManager->persist($installation);
        }

        $installation->setDetectionResult($detectionResult);

        $this->entityManager->flush();

        return $installation;
    }
}
