<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
{
    /**
     * DetectionResultHandler constructor.
     *
     * @param iterable $resultHandlers
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(
        private readonly iterable $resultHandlers,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Invoke handler.
     *
     * @throws Exception
     */
    public function __invoke(DetectionResult $detectionResult): void
    {
        try {
            $this->entityManager->getConnection()->beginTransaction();

            /** @var DetectionResultHandlerInterface $handler */
            foreach ($this->resultHandlers as $handler) {
                if ($handler->supportsType($detectionResult->getType())) {
                    $handler->handleResult($detectionResult);
                }
            }

            $this->entityManager->flush();
            $this->entityManager->clear();

            $this->entityManager->commit();
        } catch (Exception $e) {
            $this->entityManager->getConnection()->rollBack();
            throw $e;
        }
    }
}
