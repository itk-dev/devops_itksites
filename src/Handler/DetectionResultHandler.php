<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
{
    public function __construct(private iterable $resultHandlers, private EntityManagerInterface $entityManager)
    {
    }

    public function __invoke(DetectionResult $detectionResult)
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
