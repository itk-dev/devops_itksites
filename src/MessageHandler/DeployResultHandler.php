<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\DeployResult;
use App\Repository\DeployResultRepository;
use App\Repository\ServerRepository;
use App\Service\DeployResultApplier;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class DeployResultHandler
{
    public function __construct(
        private ServerRepository $serverRepository,
        private DeployResultRepository $deployResultRepository,
        private DeployResultApplier $applier,
        private EntityManagerInterface $entityManager,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DeployResult $deployResult): void
    {
        $server = $this->serverRepository->findOneBy(['name' => $deployResult->getServerName()]);

        // Pipelines only deploy to registered servers, so this should never
        // happen. The report was accepted with a 202 before this ran, so the
        // pipeline is not failed; the report is logged and dropped.
        if (null === $server) {
            $this->logger->error('Dropped deploy result for unknown server "{server}"', [
                'server' => $deployResult->getServerName(),
                'rootDir' => $deployResult->getRootDir(),
                'repoUrl' => $deployResult->getRepoUrl(),
                'tag' => $deployResult->getTag(),
                'pipelineUrl' => $deployResult->getPipelineUrl(),
            ]);

            return;
        }

        $deployResult->setServer($server);

        $hash = $deployResult->generateHash()->getHash();
        $existingResult = $this->deployResultRepository->findOneBy(['server' => $server, 'hash' => $hash]);

        // Redeploying the same tag to the same place changes nothing, so only
        // the timestamp is updated.
        if (null !== $existingResult) {
            $existingResult->setLastContact(new \DateTimeImmutable());

            $this->entityManager->flush();
            $this->entityManager->clear();

            return;
        }

        $deployResult->setLastContact(new \DateTimeImmutable());
        $this->entityManager->persist($deployResult);

        $this->applier->apply($deployResult);

        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
