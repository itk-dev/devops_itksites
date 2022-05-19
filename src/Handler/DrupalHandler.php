<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Service\PackageVersionFactory;
use App\Types\DetectionType;
use App\Types\FrameworkTypes;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "drupal".
 */
class DrupalHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param PackageVersionFactory $packageVersionFactory
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly PackageVersionFactory $packageVersionFactory)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $installationRepository = $this->entityManager->getRepository(Installation::class);

        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $installation = $installationRepository->findByRootDirAndServer($detectionResult->getRootDir(), $detectionResult->getServer());

            $installation?->setComposerVersion($data->composerVersion);
            $installation?->setFrameworkVersion($data->version);
            $installation?->setType(FrameworkTypes::DRUPAL);

            if (null !== $installation && isset($data->packages->installed)) {
                $this->packageVersionFactory->setPackageVersions($installation, $data->packages->installed);
            }

            $this->entityManager->flush();
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::DRUPAL === $type;
    }
}
