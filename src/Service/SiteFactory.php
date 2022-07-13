<?php

namespace App\Service;

use App\Entity\DetectionResult;
use App\Entity\Site;
use App\Repository\SiteRepository;
use Doctrine\ORM\EntityManagerInterface;

class SiteFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly SiteRepository $repository
    ) {
    }

    public function getSite(string $configFilePath, DetectionResult $detectionResult): Site
    {
        $site = $this->repository->findOneBy([
            'configFilePath' => $configFilePath,
            'rootDir' => $detectionResult->getRootDir(),
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $site) {
            $site = new Site();
            $this->entityManager->persist($site);

            $site->setDetectionResult($detectionResult);
            $site->setConfigFilePath($configFilePath);
        }

        $this->entityManager->flush();

        return $site;
    }
}
