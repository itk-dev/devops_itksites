<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
{
    /**
     * DetectionResultHandler constructor.
     *
     * @param iterable               $resultHandlers
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(private iterable $resultHandlers, private EntityManagerInterface $entityManager)
    {
    }

    /**
     * Invoke handler.
     */
    public function __invoke(DetectionResult $detectionResult): void
    {
        /** @var DetectionResultHandlerInterface $handler */
        foreach ($this->resultHandlers as $handler) {
            if ($handler->supportsType($detectionResult->getType())) {
                $handler->handleResult($detectionResult);
            }
        }

        $this->entityManager->flush();
    }
}
