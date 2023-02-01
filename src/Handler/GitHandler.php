<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\GitTagFactory;
use App\Service\InstallationFactory;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "git".
 */
class GitHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly InstallationFactory $installationFactory,
        private readonly GitTagFactory $gitCloneFactory,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        try {
            $data = $this->getData($detectionResult);

            if (null === $data) {
                return;
            }

            $installation = $this->installationFactory->getInstallation($detectionResult);

            $this->gitCloneFactory->setGitCloneData($installation, $data);
        } catch (\JsonException $e) {
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

        if (empty($data->remotes) || 'unknown' === strtolower($data->remotes[0])) {
            return null;
        }

        return $data;
    }
}
