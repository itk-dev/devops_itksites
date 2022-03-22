<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use Doctrine\ORM\EntityManagerInterface;

class DirectoryHandler implements DetectionResultHandlerInterface
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    public function handleResult(DetectionResult $detectionResult): void
    {
        $installationRepository = $this->entityManager->getRepository(Installation::class);

        $installation = $installationRepository->findOneBy([
            'rootDir' => $detectionResult->getRootDir(),
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $installation) {
            $installation = new Installation();
            $installation->setRootDir($detectionResult->getRootDir());
            $installation->setServer($detectionResult->getServer());

            $this->entityManager->persist($installation);
        }

        $installation->setDetectionResult($detectionResult);
    }

    public function supportsType(string $type): bool
    {
        return 'dir' === $type;
    }
}
