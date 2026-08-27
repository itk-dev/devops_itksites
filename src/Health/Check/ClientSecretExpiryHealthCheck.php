<?php

declare(strict_types=1);

namespace App\Health\Check;

use App\Health\HealthCheckInterface;
use App\Health\HealthCheckResult;
use ItkDev\OpenIdConnectBundle\Util\ClientSecretExpiryChecker;
use ItkDev\OpenIdConnectBundle\Util\ClientSecretExpiryStatus;

/**
 * Reports how close each OIDC provider's client secret is to expiring.
 *
 * An expired client secret breaks every login at once, and nothing was watching
 * for it: the token exchange starts failing with `invalid_client`, and the only
 * symptom is users bouncing between the site and Azure. The bundle knows the
 * date from `client_secret_expires_at`; this check puts it where monitoring
 * already looks.
 *
 * Only an already-expired secret is degraded. "Expiring soon" stays ok on
 * purpose: the warning window is 30 days by default, and a readiness endpoint
 * that answers 503 for a month is one nobody believes by the time it matters.
 * `days_remaining` in the detail payload is what a dashboard can alert on, and
 * the bundle logs a warning on every login in the meantime.
 *
 * The configured date is an indicator, not the authority — the identity
 * provider decides whether a secret still works. A secret rotated without
 * updating the date reports expired while logins succeed. That false degraded
 * answer is accepted: either way someone has to look at it.
 */
readonly class ClientSecretExpiryHealthCheck implements HealthCheckInterface
{
    public function __construct(
        private ClientSecretExpiryChecker $expiryChecker,
    ) {
    }

    public function getName(): string
    {
        return 'oidc_client_secret';
    }

    public function check(): HealthCheckResult
    {
        $statuses = $this->expiryChecker->getAllStatuses();

        if ([] === $statuses) {
            return HealthCheckResult::skipped($this->getName(), 'No OIDC providers are configured.');
        }

        $details = [];
        $expired = [];
        $unknown = [];

        foreach ($statuses as $providerKey => $expiry) {
            $details[$providerKey.'.status'] = $expiry->status->value;
            $details[$providerKey.'.expires_at'] = $expiry->expiresAt?->format(\DATE_ATOM);
            $details[$providerKey.'.days_remaining'] = $expiry->daysRemaining;

            if ($expiry->isExpired()) {
                $expired[] = $providerKey;
            } elseif (ClientSecretExpiryStatus::Unknown === $expiry->status) {
                $unknown[] = $providerKey;
            }
        }

        if ([] !== $expired) {
            return HealthCheckResult::degraded(
                $this->getName(),
                \sprintf('Client secret past its configured expiry: %s.', implode(', ', $expired)),
                $details
            );
        }

        // No date anywhere is the state an installation is in before it
        // configures any: nothing is being monitored, which is not the same as
        // nothing being wrong, so it is reported as skipped rather than ok.
        if (\count($unknown) === \count($statuses)) {
            return HealthCheckResult::skipped($this->getName(), 'No client secret expiry dates are configured.');
        }

        return HealthCheckResult::ok($this->getName(), $details);
    }
}
