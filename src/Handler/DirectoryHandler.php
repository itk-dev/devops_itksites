<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\Site;
use App\Repository\DetectionResultRepository;
use App\Repository\SiteRepository;
use App\Service\InstallationFactory;
use App\Types\DetectionType;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "dir" (Installations).
 */
readonly class DirectoryHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param SiteRepository $siteRepository
     * @param InstallationFactory $installationFactory
     */
    public function __construct(
        private SiteRepository $siteRepository,
        private InstallationFactory $installationFactory,
        private DetectionResultRepository $detectionResultRepository,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $server = $detectionResult->getServer();
        $oldInstallations = $server->getInstallations();

        $installations = $this->installationFactory->getInstallations($detectionResult);

        /** @var Installation $installation */
        foreach ($installations as $installation) {
            $sites = $this->siteRepository->findByRootDirAndServer(
                $installation->getRootDir(),
                $server
            );
            /* @var Site $site */
            foreach ($sites as $site) {
                $installation->addSite($site);
            }
        }

        $detectionResult->getServer()->setInstallations($installations);

        // Delete results from installations no longer seen on the server.
        foreach ($oldInstallations as $oldInstallation) {
            if (!$installations->contains($oldInstallation)) {
                $this->detectionResultRepository->deleteByInstallation($oldInstallation);
            }
        }

    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::DIRECTORY === $type;
    }

    private function getRemovedInstallations(Collection $oldInstallations, Collection $newInstallations): array
    {
        $result = [];
        foreach ($oldInstallations as $installation) {
            if (!$newInstallations->contains($installation)) {
                $result[] = $installation;
            }
        }

        return $result;
    }
}
