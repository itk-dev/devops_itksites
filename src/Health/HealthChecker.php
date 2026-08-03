<?php

declare(strict_types=1);

namespace App\Health;

use Psr\Log\LoggerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Runs the registered health checks and caches the outcome.
 *
 * The health endpoints are polled by monitoring, so every check result is
 * cached for a few seconds. Without it a monitor (or anyone who found the URL)
 * could turn the endpoint into a query amplifier against the very dependencies
 * it reports on.
 *
 * The pool is the dedicated, filesystem-backed cache.health pool: it must keep
 * working while the database and the message queue are down, which is exactly
 * when these endpoints matter. See config/packages/cache.yaml.
 */
readonly class HealthChecker
{
    private const string CACHE_KEY = 'app.health.results';

    /**
     * @param iterable<HealthCheckInterface> $checks
     */
    public function __construct(
        private iterable $checks,
        private CacheInterface $cache,
        private LoggerInterface $logger,
        private int $cacheTtl,
    ) {
    }

    /**
     * @return array<HealthCheckResult>
     */
    public function run(): array
    {
        return $this->cache->get(self::CACHE_KEY, function (ItemInterface $item): array {
            $item->expiresAfter($this->cacheTtl);

            $results = [];
            foreach ($this->checks as $check) {
                $results[] = $this->runCheck($check);
            }

            return $results;
        });
    }

    /**
     * @param array<HealthCheckResult> $results
     */
    public function isHealthy(array $results): bool
    {
        foreach ($results as $result) {
            if ($result->isDegraded()) {
                return false;
            }
        }

        return true;
    }

    /**
     * Run a check, turning an unexpected failure into a degraded result.
     *
     * A check that throws must not take down the endpoint that reports on it.
     */
    private function runCheck(HealthCheckInterface $check): HealthCheckResult
    {
        try {
            return $check->check();
        } catch (\Throwable $throwable) {
            $this->logger->error('Health check "{check}" threw an exception: {message}', [
                'check' => $check->getName(),
                'message' => $throwable->getMessage(),
                'exception' => $throwable,
            ]);

            return HealthCheckResult::degraded($check->getName(), 'The check failed unexpectedly.');
        }
    }
}
