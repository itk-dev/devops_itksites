<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Site;
use App\Service\InstallationFactory;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "dir".
 */
class DirectoryHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param InstallationFactory $factory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InstallationFactory $factory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $siteRepository = $this->entityManager->getRepository(Site::class);

        $installation = $this->factory->getInstallation($detectionResult);

        $sites = $siteRepository->findByRootDirAndServer($detectionResult->getRootDir(), $detectionResult->getServer());
        foreach ($sites as $site) {
            /* @var Site $site */
            $installation->addSite($site);
        }

        $installation->setDetectionResult($detectionResult);
        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::DIRECTORY === $type;
    }
}
