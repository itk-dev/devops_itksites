<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\DeployResult;
use App\Service\DeployResultApplier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Deploy results are a source record, like detection results: the derived tables
 * are rebuilt from them. Replay after replaying detection results, which is what
 * recreates the installations the tags are attached to.
 */
#[AsCommand(
    name: 'app:replay:deploy-results',
    description: 'Replay all deploy results to trigger re-processing',
)]
readonly class ReplayDeployResultsCommand
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private DeployResultApplier $applier,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option(description: 'Maximum number of deploy results to replay')]
        ?int $limit = null,
    ): int {
        $queryBuilder = $this->entityManager->createQueryBuilder()
            ->select('r')
            ->from(DeployResult::class, 'r')
            // Ulid ids are time ordered, so this replays in the order the
            // deployments originally arrived.
            ->orderBy('r.id', 'ASC');

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        $count = 0;

        foreach ($queryBuilder->getQuery()->toIterable() as $deployResult) {
            $this->applier->apply($deployResult);
            ++$count;
        }

        $this->entityManager->flush();

        $io->success(sprintf('Replayed %d deploy result(s)', $count));

        return Command::SUCCESS;
    }
}
