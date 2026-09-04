<?php

declare(strict_types=1);

namespace App\Health;

/**
 * A single dependency check run by the health endpoints.
 *
 * Implementations are auto-tagged and collected by HealthChecker, mirroring the
 * DetectionResultHandlerInterface convention.
 *
 * Implementations should return a degraded result rather than throwing, but
 * HealthChecker guards against throwing implementations regardless.
 */
interface HealthCheckInterface
{
    /**
     * Stable machine-readable key, used in the detail payload.
     */
    public function getName(): string;

    public function check(): HealthCheckResult;
}
