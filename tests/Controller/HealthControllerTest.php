<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class HealthControllerTest extends WebTestCase
{
    use RefreshDatabaseTrait;

    /**
     * Liveness must never depend on anything, so it is always 200.
     */
    public function testLiveIsAlwaysOk(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health/live');

        $this->assertResponseIsSuccessful();
        $this->assertSame(['status' => 'ok'], $this->decode($client->getResponse()));
    }

    /**
     * All three endpoints must bypass the firewalls. A redirect here means the
     * OIDC entry point has caught them, which is what made a total ingest
     * outage look healthy to monitoring.
     */
    #[DataProvider('endpointProvider')]
    public function testEndpointIsPubliclyReachable(string $path): void
    {
        $client = static::createClient();
        $client->request('GET', $path);

        $this->assertContains(
            $client->getResponse()->getStatusCode(),
            [Response::HTTP_OK, Response::HTTP_SERVICE_UNAVAILABLE],
            \sprintf('%s should answer 200 or 503, never a redirect to login.', $path)
        );
    }

    /**
     * @return iterable<array{string}>
     */
    public static function endpointProvider(): iterable
    {
        yield ['/health/live'];
        yield ['/health/ready'];
        yield ['/health/detail'];
    }

    /**
     * Readiness is public, so it must disclose the aggregated status and
     * nothing else — no check names, no messages, no dependency detail.
     */
    public function testReadyDisclosesNothingBeyondStatus(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health/ready');

        $payload = $this->decode($client->getResponse());

        $this->assertSame(['status'], array_keys($payload));
        $this->assertContains($payload['status'], ['ok', 'degraded']);
    }

    public function testDetailReportsEveryCheck(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health/detail');

        $payload = $this->decode($client->getResponse());

        $this->assertArrayHasKey('checks', $payload);
        $this->assertEqualsCanonicalizing(
            ['database', 'rabbitmq', 'ingest_freshness'],
            array_keys($payload['checks'])
        );

        foreach ($payload['checks'] as $check) {
            $this->assertContains($check['status'], ['ok', 'degraded', 'skipped']);
        }
    }

    public function testResponsesAreNotCacheableByClients(): void
    {
        $client = static::createClient();
        $client->request('GET', '/health/ready');

        $this->assertResponseHeaderSame('Cache-Control', 'no-store, private');
    }

    /**
     * @return array<string, mixed>
     */
    private function decode(Response $response): array
    {
        $content = $response->getContent();
        $this->assertIsString($content);

        $payload = json_decode($content, true, 512, \JSON_THROW_ON_ERROR);
        $this->assertIsArray($payload);

        return $payload;
    }
}
