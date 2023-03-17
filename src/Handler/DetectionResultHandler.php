<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DetectionResultHandler
{
    private const MAX_RETRIES = 3;

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
        $this->entityManager->getConnection()->beginTransaction();

        try {
            /** @var DetectionResultHandlerInterface $handler */
            foreach ($this->resultHandlers as $handler) {
                if ($handler->supportsType($detectionResult->getType())) {
                    $handler->handleResult($detectionResult);
                }
            }

            $this->entityManager->flush();

            $this->entityManager->getConnection()->commit();
        } catch (\Exception $exception) {
            $this->entityManager->getConnection()->rollBack();

            // Re-throw to let the messenger retry config handle reties.
            throw $exception;
        }

        $this->entityManager->clear();
    }
}
