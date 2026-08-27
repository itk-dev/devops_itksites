<?php

declare(strict_types=1);

namespace App\Tests\Health\Check;

use App\Health\Check\ClientSecretExpiryHealthCheck;
use App\Health\HealthCheckResult;
use App\Health\HealthStatus;
use ItkDev\OpenIdConnectBundle\Util\ClientSecretExpiryChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Clock\MockClock;

/**
 * The real ClientSecretExpiryChecker is used with a fixed clock rather than a
 * double: the mapping from its statuses to a health result is the whole point of
 * the check, and a double would assert that mapping against itself.
 */
class ClientSecretExpiryHealthCheckTest extends TestCase
{
    private const int WARNING_DAYS = 30;

    /**
     * A secret past its date breaks every login, so readiness must fail.
     */
    public function testExpiredSecretIsDegraded(): void
    {
        $result = $this->check(['azure_az' => '2026-08-01']);

        self::assertSame(HealthStatus::Degraded, $result->status);
        self::assertSame('Client secret past its configured expiry: azure_az.', $result->message);
        self::assertSame('expired', $result->details['azure_az.status']);
        self::assertLessThan(0, $result->details['azure_az.days_remaining']);
    }

    /**
     * Expiring soon stays ok. Thirty days of 503 would train everyone to ignore
     * the endpoint before the day it matters; the remaining days are in the
     * payload for whatever watches it.
     */
    public function testExpiringSoonIsOkWithDaysRemaining(): void
    {
        $result = $this->check(['azure_az' => '2026-09-05']);

        self::assertSame(HealthStatus::Ok, $result->status);
        self::assertSame('expiring_soon', $result->details['azure_az.status']);
        // 2026-08-26 12:00 UTC to midnight on 2026-09-05 is 9.5 days, floored.
        self::assertSame(9, $result->details['azure_az.days_remaining']);
    }

    public function testHealthySecretIsOk(): void
    {
        $result = $this->check(['azure_az' => '2027-01-31']);

        self::assertSame(HealthStatus::Ok, $result->status);
        self::assertSame('ok', $result->details['azure_az.status']);
        self::assertSame('2027-01-31T00:00:00+00:00', $result->details['azure_az.expires_at']);
    }

    /**
     * An unconfigured date means nothing is being monitored, which must not read
     * as healthy and must not fail readiness either.
     */
    public function testUnconfiguredDateIsSkipped(): void
    {
        $result = $this->check(['azure_az' => null]);

        self::assertSame(HealthStatus::Skipped, $result->status);
        self::assertSame('No client secret expiry dates are configured.', $result->message);
    }

    public function testNoProvidersIsSkipped(): void
    {
        $result = $this->check([]);

        self::assertSame(HealthStatus::Skipped, $result->status);
        self::assertSame('No OIDC providers are configured.', $result->message);
    }

    /**
     * One expired provider degrades the check even when another is fine, and
     * both stay visible in the payload.
     */
    public function testExpiredProviderDegradesAlongsideAHealthyOne(): void
    {
        $result = $this->check(['azure_az' => '2027-01-31', 'legacy' => '2026-08-01']);

        self::assertSame(HealthStatus::Degraded, $result->status);
        self::assertSame('Client secret past its configured expiry: legacy.', $result->message);
        self::assertSame('ok', $result->details['azure_az.status']);
        self::assertSame('expired', $result->details['legacy.status']);
    }

    /**
     * @param array<string, string|null> $expiryDates
     */
    private function check(array $expiryDates): HealthCheckResult
    {
        $checker = new ClientSecretExpiryChecker(
            new MockClock('2026-08-26 12:00:00', 'UTC'),
            $expiryDates,
            self::WARNING_DAYS,
            new NullLogger(),
        );

        return (new ClientSecretExpiryHealthCheck($checker))->check();
    }
}
