<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\GitTagFactory;
use App\Service\InstallationFactory;
use App\Types\DetectionType;

/**
 * Handler for DetectionResult off type "git".
 */
readonly class GitHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param InstallationFactory $installationFactory
     * @param GitTagFactory $gitCloneFactory
     */
    public function __construct(
        private InstallationFactory $installationFactory,
        private GitTagFactory $gitCloneFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        try {
            $data = $this->getData($detectionResult);
            $installation = $this->installationFactory->getInstallation($detectionResult);

            if (null === $data) {
                $installation->setGitTag(null);

                return;
            }

            $this->gitCloneFactory->setGitCloneData($installation, $data);
        } catch (\JsonException) {
            // @TODO log exceptions
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::GIT === $type;
    }

    /**
     * Get data if remotes are set.
     *
     * The git harvester will send an empty result even for
     * "fatal: not a git repository (or any parent up to mount point /)"
     *
     * @param DetectionResult $result
     *
     * @return object|null
     *
     * @throws \JsonException
     */
    private function getData(DetectionResult $result): ?object
    {
        if (empty($result->getData())) {
            return null;
        }

        $data = \json_decode($result->getData(), false, 512, JSON_THROW_ON_ERROR);

        if (empty($data->remotes) || 'unknown' === strtolower((string) $data->remotes[0])) {
            return null;
        }

        return $data;
    }
}
