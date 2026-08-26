<?php

declare(strict_types=1);

namespace App\Controller;

use App\Health\HealthChecker;
use App\Health\HealthStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Health endpoints, in three tiers.
 *
 * /health/live    Public. No dependencies at all. Says only that PHP-FPM is up
 *                 and routing works. It must never touch the database: a
 *                 liveness probe that fails during a database outage makes an
 *                 orchestrator restart a container that is not the problem.
 *
 * /health/ready   Public, but opaque. Runs every check and answers 200 or 503
 *                 with the aggregated status only. The status code is the
 *                 payload; which dependency failed is not disclosed. This is
 *                 the endpoint monitoring should watch.
 *
 * /health/detail  Per-check results, timings, queue depth. Discloses internals
 *                 and MUST be protected at the edge — see the ITKBasicAuth
 *                 middleware on the nginx service in docker-compose.server.yml.
 *
 * Authentication deliberately happens in Traefik rather than in Symfony. Both
 * user providers in config/packages/security.yaml are Doctrine entity
 * providers, so an application-level firewall on these routes would fail to
 * authenticate during a database outage and answer 500 — precisely when the
 * endpoint needs to answer "the database is down". ^/health is therefore
 * excluded from the Symfony firewalls entirely.
 */
readonly class HealthController
{
    public function __construct(
        private HealthChecker $healthChecker,
    ) {
    }

    #[Route('/health/live', name: 'app_health_live', methods: ['GET'])]
    public function live(): JsonResponse
    {
        return $this->respond(['status' => HealthStatus::Ok->value], true);
    }

    #[Route('/health/ready', name: 'app_health_ready', methods: ['GET'])]
    public function ready(): JsonResponse
    {
        $healthy = $this->healthChecker->isHealthy($this->healthChecker->run());

        return $this->respond(
            ['status' => $healthy ? HealthStatus::Ok->value : HealthStatus::Degraded->value],
            $healthy
        );
    }

    #[Route('/health/detail', name: 'app_health_detail', methods: ['GET'])]
    public function detail(): JsonResponse
    {
        $results = $this->healthChecker->run();
        $healthy = $this->healthChecker->isHealthy($results);

        $checks = [];
        foreach ($results as $result) {
            $checks[$result->name] = array_filter([
                'status' => $result->status->value,
                'message' => $result->message,
                'details' => $result->details,
            ], static fn (mixed $value): bool => null !== $value && [] !== $value);
        }

        return $this->respond([
            'status' => $healthy ? HealthStatus::Ok->value : HealthStatus::Degraded->value,
            'checks' => $checks,
        ], $healthy);
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function respond(array $payload, bool $healthy): JsonResponse
    {
        $response = new JsonResponse(
            $payload,
            $healthy ? Response::HTTP_OK : Response::HTTP_SERVICE_UNAVAILABLE
        );

        // Health responses are cached inside HealthChecker, never by the client
        // or an intermediary.
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
