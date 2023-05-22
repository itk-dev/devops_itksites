<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Site;
use App\Repository\SiteRepository;
use App\Service\InstallationFactory;
use App\Types\DetectionType;

/**
 * Handler for DetectionResult off type "dir" (Installations).
 */
class DirectoryHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param SiteRepository $siteRepository
     * @param InstallationFactory $installationFactory
     */
    public function __construct(
        private readonly SiteRepository $siteRepository,
        private readonly InstallationFactory $installationFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $installations = $this->installationFactory->getInstallations($detectionResult);

        foreach ($installations as $installation) {
            $sites = $this->siteRepository->findByRootDirAndServer(
                $detectionResult->getRootDir(),
                $detectionResult->getServer()
            );
            foreach ($sites as $site) {
                /* @var Site $site */
                $installation->addSite($site);
            }
        }

        $detectionResult->getServer()->setInstallations($installations);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::DIRECTORY === $type;
    }
}
