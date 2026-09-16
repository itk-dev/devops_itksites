<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DeployResult;
use App\Repository\InstallationRepository;
use App\Types\CodeSourceType;

/**
 * Applies a deploy result to the installation it describes.
 *
 * Kept apart from the message handler so that stored deploy results can be
 * replayed after the derived tables have been rebuilt.
 */
readonly class DeployResultApplier
{
    public function __construct(
        private InstallationRepository $installationRepository,
        private GitTagFactory $gitTagFactory,
    ) {
    }

    public function apply(DeployResult $deployResult): void
    {
        // An installation cannot be created from here: it requires a detection
        // result, and one invented by a deployment would be removed by the next
        // directory scan anyway. A site deployed before the harvester has ever
        // scanned it is picked up by the next deployment, or by a replay.
        $installation = $this->installationRepository->findOneBy([
            'rootDir' => $deployResult->getRootDir(),
            'server' => $deployResult->getServer(),
        ]);

        if (null === $installation) {
            return;
        }

        $gitTag = $this->gitTagFactory->resolveGitTag($deployResult->getRepoUrl(), $deployResult->getTag());

        if (null === $gitTag) {
            return;
        }

        $gitTag->addInstallation($installation);

        // Deliberately no git changes: a deployment has no working copy, so it
        // cannot say whether one is clean, and "0 changes" reads as a claim that
        // it is.
        $installation->setCodeSource(CodeSourceType::ARTIFACT);
    }
}
