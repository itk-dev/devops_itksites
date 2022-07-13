<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\InstallationFactory;
use App\Service\ModuleVersionFactory;
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
     * @param InstallationFactory $installationFactory
     * @param PackageVersionFactory $packageVersionFactory
     * @param ModuleVersionFactory $moduleVersionFactory
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InstallationFactory $installationFactory,
        private readonly PackageVersionFactory $packageVersionFactory,
        private readonly ModuleVersionFactory $moduleVersionFactory
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $installation = $this->installationFactory->getInstallation($detectionResult);

            $installation->setComposerVersion($data->composerVersion);
            $installation->setFrameworkVersion($data->version);
            $installation->setType(FrameworkTypes::DRUPAL);

            if (isset($data->packages->installed)) {
                $this->packageVersionFactory->setPackageVersions($installation, $data->packages->installed);
            }

            if (isset($data->modules)) {
                $this->moduleVersionFactory->setModuleVersions($installation, $data->modules);
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
