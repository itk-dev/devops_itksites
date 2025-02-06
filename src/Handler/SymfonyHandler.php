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

            $installation->setComposerVersion($data->composerVersion ?? null);
            $installation->setFrameworkVersion($data->symfony->version ?? null);
            $installation->setEol($data->symfony->eof ?? '');
            if (isset($data->symfony->lts)) {
                $lts = 'Yes' === $data->symfony->lts;
                $installation->setLts($lts);
            }
            $installation->setPhpVersion($data->symfony->phpVersion ?? null);

            if (isset($data->packages->installed)) {
                $this->packageVersionFactory->setPackageVersions($installation, $data->packages->installed);
            } else {
                $this->packageVersionFactory->setPackageVersions($installation, []);
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
