<?php

declare(strict_types=1);

namespace App\Health;

/**
 * Outcome of a single health check.
 */
enum HealthStatus: string
{
    case Ok = 'ok';
    case Degraded = 'degraded';

    /**
     * The dependency is not configured for this installation, so it is neither
     * healthy nor unhealthy and must not affect the aggregated status.
     */
    case Skipped = 'skipped';
}
