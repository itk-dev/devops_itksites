<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Service\PackageVersionFactory;
use App\Types\DetectionType;
use App\Types\FrameworkTypes;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "symfony".
 */
class SymfonyHandler implements DetectionResultHandlerInterface
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

            if (isset($data->composerVersion)) {
                $installation?->setComposerVersion($data->composerVersion);
            }
            if (isset($data->symfony->version)) {
                $installation?->setFrameworkVersion($data->symfony->version);
            }
            if (isset($data->symfony->eof)) {
                $installation?->setEof($data->symfony->eof);
            }
            if (isset($data->symfony->lts)) {
                $lts = 'Yes' === $data->symfony->lts;
                $installation?->setLts($lts);
            }
            if (isset($data->symfony->phpVersion)) {
                $installation?->setPhpVersion($data->symfony->phpVersion);
            }
            $installation?->setType(FrameworkTypes::SYMFONY);

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
        return DetectionType::SYMFONY === $type;
    }
}
