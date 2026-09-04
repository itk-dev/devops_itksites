<?php

declare(strict_types=1);

namespace App\Health\Check;

use App\Health\HealthCheckInterface;
use App\Health\HealthCheckResult;
use App\Repository\DetectionResultRepository;
use Psr\Log\LoggerInterface;

/**
 * Verifies that detection results are still arriving from the harvester.
 *
 * This is the check that catches the failure the dependency checks cannot see
 * as a whole: a dead harvester, a stalled messenger consumer or a wedged broker
 * all leave the application itself perfectly able to serve requests while
 * nothing is actually being ingested.
 *
 * lastContact is used rather than createdAt because identical submissions are
 * deduplicated by content hash and only bump lastContact — a harvester that
 * keeps reporting unchanged servers is still healthy.
 */
readonly class IngestFreshnessHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private DetectionResultRepository $repository,
        private LoggerInterface $logger,
        private int $maxAgeSeconds,
    ) {
    }

    public function getName(): string
    {
        return 'ingest_freshness';
    }

    public function check(): HealthCheckResult
    {
        try {
            $lastContact = $this->repository->findLastContact();
        } catch (\Throwable $throwable) {
            $this->logger->error('Health check "ingest_freshness" failed: {message}', [
                'message' => $throwable->getMessage(),
                'exception' => $throwable,
            ]);

            // Almost certainly the database being down, which the database
            // check reports separately.
            return HealthCheckResult::degraded($this->getName(), 'Unable to query the last detection result.');
        }

        if (!$lastContact instanceof \DateTimeImmutable) {
            return HealthCheckResult::degraded(
                $this->getName(),
                'No detection results have been received yet.',
                ['max_age_seconds' => $this->maxAgeSeconds]
            );
        }

        $ageSeconds = time() - $lastContact->getTimestamp();
        $details = [
            'last_contact' => $lastContact->format(\DATE_ATOM),
            'age_seconds' => $ageSeconds,
            'max_age_seconds' => $this->maxAgeSeconds,
        ];

        if ($ageSeconds > $this->maxAgeSeconds) {
            return HealthCheckResult::degraded(
                $this->getName(),
                \sprintf('No detection result received for %d seconds.', $ageSeconds),
                $details
            );
        }

        return HealthCheckResult::ok($this->getName(), $details);
    }
}
