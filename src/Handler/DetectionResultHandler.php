<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Exception\RetryableException;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

final class DetectionResultHandler implements MessageHandlerInterface
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
     * @throws RetryableException
     */
    public function __invoke(DetectionResult $detectionResult): void
    {
        $retries = 0;

        do {
            $this->entityManager->getConnection()->beginTransaction();

            try {
                /** @var DetectionResultHandlerInterface $handler */
                foreach ($this->resultHandlers as $handler) {
                    if ($handler->supportsType($detectionResult->getType())) {
                        $handler->handleResult($detectionResult);
                    }
                }

                $this->entityManager->flush();
                $this->entityManager->commit();

                break;
            } catch (Exception\RetryableException $retryableException) {
                $this->entityManager->getConnection()->rollBack();
                ++$retries;

                if (self::MAX_RETRIES === $retries) {
                    throw $retryableException;
                }

                \usleep(100000);
            } catch (\Exception $exception) {
                $this->entityManager->getConnection()->rollBack();

                throw $exception;
            }
        } while ($retries < self::MAX_RETRIES);

        $this->entityManager->clear();
    }
}
