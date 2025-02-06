<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\PersistDetectionResult;
use App\Message\ProcessDetectionResult;
use App\Repository\DetectionResultRepository;
use App\Repository\ServerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final readonly class PersistDetectionResultHandler
{
    public function __construct(
        private ServerRepository $serverRepository,
        private DetectionResultRepository $detectionResultRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(PersistDetectionResult $message): void
    {
        $server = $this->serverRepository->findOneBy(['apiKey' => $message->serverApiKey]);

        if (null === $server) {
            throw new UnrecoverableMessageHandlingException('Server not found.');
        }

        $server->updateLastContactAt($message->receivedAt);

        $result = $message->detectionResult;
        $result->setServer($server);

        $hash = $result->generateHash()->getHash();
        $existingResult = $this->detectionResultRepository->findOneBy(['server' => $server, 'hash' => $hash]);

        // New results should trigger cleanup and be sent to the queue for further processing.
        if (null === $existingResult) {
            $this->entityManager->persist($result);
            $result->setLastContact($message->receivedAt);

            $this->entityManager->flush();

            $id = $result->getId();
            if (null !== $id) {
                $this->messageBus->dispatch(
                    new ProcessDetectionResult($id)
                );
            }
        // Existing results will only update timestamps
        } else {
            $existingResult->setLastContact($message->receivedAt);

            $this->entityManager->flush();
        }

        $this->entityManager->clear();
    }
}
