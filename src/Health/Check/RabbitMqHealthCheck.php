<?php

declare(strict_types=1);

namespace App\Health\Check;

use App\Health\HealthCheckInterface;
use App\Health\HealthCheckResult;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Transport\Receiver\MessageCountAwareInterface;
use Symfony\Component\Messenger\Transport\TransportInterface;

/**
 * Verifies that the async messenger transport is reachable.
 *
 * The transport is RabbitMQ on staging and production (see
 * MESSENGER_TRANSPORT_DSN). Counting the queued messages is the cheapest
 * operation that forces an actual connection to the broker, and the count
 * doubles as a backlog signal: a queue that only grows means the supervisor
 * consumer has stopped even though the broker itself is fine.
 */
readonly class RabbitMqHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private TransportInterface $transport,
        private LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'rabbitmq';
    }

    public function check(): HealthCheckResult
    {
        if (!$this->transport instanceof MessageCountAwareInterface) {
            return HealthCheckResult::skipped(
                $this->getName(),
                'The configured transport does not support message counting.'
            );
        }

        try {
            $count = $this->transport->getMessageCount();
        } catch (\Throwable $throwable) {
            $this->logger->error('Health check "rabbitmq" failed: {message}', [
                'message' => $throwable->getMessage(),
                'exception' => $throwable,
            ]);

            return HealthCheckResult::degraded($this->getName(), 'Unable to reach the message queue.');
        }

        return HealthCheckResult::ok($this->getName(), ['queued_messages' => $count]);
    }
}
