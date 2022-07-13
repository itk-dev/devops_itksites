<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
{
    /**
     * DetectionResultHandler constructor.
     *
     * @param iterable $resultHandlers
     */
    public function __construct(
        private readonly iterable $resultHandlers,
    ) {
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
    }
}
