<?php

declare(strict_types=1);

namespace App\Health;

/**
 * The result of running a single health check.
 *
 * Messages and details are only ever exposed through the authenticated detail
 * endpoint. The public readiness endpoint reports the aggregated status alone.
 */
readonly class HealthCheckResult
{
    /**
     * @param array<string, scalar|null> $details
     */
    private function __construct(
        public string $name,
        public HealthStatus $status,
        public ?string $message = null,
        public array $details = [],
    ) {
    }

    /**
     * @param array<string, scalar|null> $details
     */
    public static function ok(string $name, array $details = []): self
    {
        return new self($name, HealthStatus::Ok, null, $details);
    }

    /**
     * @param array<string, scalar|null> $details
     */
    public static function degraded(string $name, string $message, array $details = []): self
    {
        return new self($name, HealthStatus::Degraded, $message, $details);
    }

    public static function skipped(string $name, string $message): self
    {
        return new self($name, HealthStatus::Skipped, $message);
    }

    public function isDegraded(): bool
    {
        return HealthStatus::Degraded === $this->status;
    }
}
