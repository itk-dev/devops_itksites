<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Message\PersistDetectionResult;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DetectionResultHandler
{
    /**
     * DetectionResultHandler constructor.
     *
     * @param Security $security
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        private readonly Security $security,
        private readonly MessageBusInterface $messageBus,
    ) {
    }

    /**
     * Invoke handler.
     */
    public function __invoke(DetectionResult $detectionResult): void
    {
        $server = $this->security->getUser();

        if (!$server instanceof Server) {
            return;
        }

        $this->messageBus->dispatch(
            new PersistDetectionResult(
                detectionResult: $detectionResult,
                serverApiKey: $server->getUserIdentifier(),
                receivedAt: new \DateTimeImmutable()
            )
        );
    }
}
