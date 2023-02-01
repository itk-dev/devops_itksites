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
        private readonly SiteRepository $repository,
        private readonly InstallationFactory $installationFactory,
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

            $installation = $this->installationFactory->getInstallation($detectionResult);
            $site->setInstallation($installation);
            $site->setDetectionResult($detectionResult);
            $site->setConfigFilePath($configFilePath);
        }

        return $site;
    }
}
