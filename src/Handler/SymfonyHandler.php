<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\InstallationFactory;
use App\Service\PackageVersionFactory;
use App\Types\DetectionType;
use App\Types\FrameworkTypes;

/**
 * Handler for DetectionResult off type "symfony".
 */
readonly class SymfonyHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param PackageVersionFactory $packageVersionFactory
     * @param InstallationFactory $installationFactory
     */
    public function __construct(
        private PackageVersionFactory $packageVersionFactory,
        private InstallationFactory $installationFactory,
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
            $installation->setType(FrameworkTypes::SYMFONY);

            if (isset($data->composerVersion)) {
                $installation->setComposerVersion($data->composerVersion);
            }
            if (isset($data->symfony->version)) {
                $installation->setFrameworkVersion($data->symfony->version);
            }
            if (isset($data->symfony->eof)) {
                $installation->setEol($data->symfony->eof);
            }
            if (isset($data->symfony->lts)) {
                $lts = 'Yes' === $data->symfony->lts;
                $installation->setLts($lts);
            }
            if (isset($data->symfony->phpVersion)) {
                $installation->setPhpVersion($data->symfony->phpVersion);
            }

            if (isset($data->packages->installed)) {
                $this->packageVersionFactory->setPackageVersions($installation, $data->packages->installed);
            }
        } catch (\JsonException) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::SYMFONY === $type;
    }
}
