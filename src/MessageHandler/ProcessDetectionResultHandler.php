<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Handler\DetectionResultHandlerInterface;
use App\Message\ProcessDetectionResult;
use App\Repository\DetectionResultRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ProcessDetectionResultHandler
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DetectionResultRepository $detectionResultRepository,
        private readonly iterable $resultHandlers,
    ) {
    }

    public function __invoke(ProcessDetectionResult $message): void
    {
        $detectionResult = $this->detectionResultRepository->find($message->detectionResultId);

        if (null !== $detectionResult) {
            /** @var DetectionResultHandlerInterface $handler */
            foreach ($this->resultHandlers as $handler) {
                if ($handler->supportsType($detectionResult->getType())) {
                    $handler->handleResult($detectionResult);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();
            gc_collect_cycles();
        }
    }
}
