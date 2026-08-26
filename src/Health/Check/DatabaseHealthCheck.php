<?php

declare(strict_types=1);

namespace App\Health\Check;

use App\Health\HealthCheckInterface;
use App\Health\HealthCheckResult;
use Doctrine\DBAL\Connection;
use Psr\Log\LoggerInterface;

/**
 * Verifies that the database accepts connections and answers queries.
 */
readonly class DatabaseHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private Connection $connection,
        private LoggerInterface $logger,
    ) {
    }

    public function getName(): string
    {
        return 'database';
    }

    public function check(): HealthCheckResult
    {
        $start = microtime(true);

        try {
            $this->connection->executeQuery('SELECT 1');
        } catch (\Throwable $throwable) {
            $this->logger->error('Health check "database" failed: {message}', [
                'message' => $throwable->getMessage(),
                'exception' => $throwable,
            ]);

            // The caller gets no detail; the reason stays in the logs.
            return HealthCheckResult::degraded($this->getName(), 'Unable to query the database.');
        }

        return HealthCheckResult::ok($this->getName(), [
            'response_time_ms' => round((microtime(true) - $start) * 1000, 1),
        ]);
    }
}
