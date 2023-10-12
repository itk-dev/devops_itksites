<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PersistDetectionResult;
use App\Message\ProcessDetectionResult;
use App\Repository\DetectionResultRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class PersistDetectionResultHandler
{
    public function __construct(
        private readonly ServerRepository $serverRepository,
        private readonly DetectionResultRepository $detectionResultRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(PersistDetectionResult $message): void
    {
        $server = $this->serverRepository->findOneBy(['apiKey' => $message->serverApiKey]);
        $detectionResult = $message->detectionResult;

        $detectionResult->setServer($server);

        $hash = $detectionResult->generateHash()->getHash();
        $result = $this->detectionResultRepository->findOneBy(['server' => $server, 'hash' => $hash]);

        if (null === $result) {
            $this->entityManager->persist($detectionResult);
            $detectionResult->setLastContact($message->receivedAt);
        } else {
            $result->setLastContact($message->receivedAt);
        }

        $this->entityManager->flush();

        // New DetectionResults should be sent to processing
        if (null === $result) {
            $id = $detectionResult->getId();
            if (null !== $id) {
                $this->messageBus->dispatch(
                    new ProcessDetectionResult($id)
                );
            }
        }

        $this->entityManager->clear();
    }
}
