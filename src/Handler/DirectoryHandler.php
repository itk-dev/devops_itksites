<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\Site;
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
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $installationRepository = $this->entityManager->getRepository(Installation::class);
        $siteRepository = $this->entityManager->getRepository(Site::class);

        $installation = $installationRepository->findOneBy([
            'rootDir' => $detectionResult->getRootDir(),
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $installation) {
            $installation = new Installation();
            $this->entityManager->persist($installation);
        }

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
