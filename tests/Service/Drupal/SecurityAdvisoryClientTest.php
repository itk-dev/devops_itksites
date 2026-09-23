<?php

declare(strict_types=1);

namespace App\Tests\Service\Drupal;

use App\Service\Drupal\SecurityAdvisoryClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class SecurityAdvisoryClientTest extends TestCase
{
    private const string FIXTURES = __DIR__.'/../../fixtures/drupal/';

    /** @var list<string> */
    private array $urls = [];

    /** @var list<int> */
    private array $ttls = [];

    public function testFetchParsesAdvisories(): void
    {
        $client = new SecurityAdvisoryClient($this->http('project-key_auth.json', 'sa-key_auth.json'), new ArrayAdapter());

        $advisories = $client->fetch('key_auth');

        self::assertSame([
            'https://www.drupal.org/api-d7/node.json?type=project_module&field_project_machine_name=key_auth',
            'https://www.drupal.org/api-d7/node.json?type=sa&field_project=2959220',
        ], $this->urls);
        self::assertCount(1, $advisories);
        $advisory = $advisories[0];
        self::assertSame('SA-CONTRIB-2026-138', $advisory->advisoryId);
        self::assertSame('https://www.drupal.org/sa-contrib-2026-138', $advisory->url);
        self::assertSame('CVE-2026-87940', $advisory->cve);
        self::assertSame('Key auth - Moderately critical - Access bypass - SA-CONTRIB-2026-138', $advisory->title);
        self::assertSame('<2.2.4', $advisory->affectedVersions);
        self::assertSame(1788974270, $advisory->created->getTimestamp());
    }

    public function testFetchSkipsNodesWithoutAdvisoryId(): void
    {
        $psa = (string) json_encode(['list' => [
            ['title' => 'Drupal core - Public service announcement - PSA-2026-01-01', 'url' => 'https://www.drupal.org/psa-2026-01-01'],
            ['title' => 'Drupal core - Critical - SA-CORE-2026-001', 'url' => 'https://www.drupal.org/sa-core-2026-001', 'field_sa_cve' => [], 'created' => '1'],
        ]]);
        $http = new MockHttpClient([
            new MockResponse((string) file_get_contents(self::FIXTURES.'project-key_auth.json')),
            new MockResponse($psa),
        ], 'https://www.drupal.org/');

        $advisories = new SecurityAdvisoryClient($http, new ArrayAdapter())->fetch('key_auth');

        self::assertCount(1, $advisories);
        self::assertSame('SA-CORE-2026-001', $advisories[0]->advisoryId);
        self::assertNull($advisories[0]->cve);
    }

    public function testFetchReturnsEmptyForNonProject(): void
    {
        $http = $this->http('project-none.json');
        $client = new SecurityAdvisoryClient($http, new ArrayAdapter());

        self::assertSame([], $client->fetch('not_a_real_project_8406'));
        self::assertSame([], $client->fetch('not_a_real_project_8406'));
        self::assertSame(1, $http->getRequestsCount());
    }

    public function testFetchIsCached(): void
    {
        $http = $this->http('project-key_auth.json', 'sa-key_auth.json');
        $client = new SecurityAdvisoryClient($http, new ArrayAdapter());

        $client->fetch('key_auth');
        self::assertCount(1, $client->fetch('key_auth'));
        self::assertSame(2, $http->getRequestsCount());
    }

    public function testCacheTtls(): void
    {
        $client = new SecurityAdvisoryClient($this->http('project-key_auth.json', 'sa-key_auth.json'), $this->ttlRecordingCache());

        $client->fetch('key_auth');

        self::assertSame([30 * 86400, 12 * 3600], $this->ttls);
    }

    public function testNotAProjectTtl(): void
    {
        $client = new SecurityAdvisoryClient($this->http('project-none.json'), $this->ttlRecordingCache());

        $client->fetch('not_a_real_project_8406');

        self::assertSame([7 * 86400], $this->ttls);
    }

    /**
     * A cache that always misses and records the TTL of each item in $this->ttls.
     */
    private function ttlRecordingCache(): CacheInterface
    {
        $item = $this->createStub(ItemInterface::class);
        $item->method('expiresAfter')->willReturnCallback(function (int $ttl) use ($item): ItemInterface {
            $this->ttls[] = $ttl;

            return $item;
        });
        $cache = $this->createStub(CacheInterface::class);
        $cache->method('get')->willReturnCallback(static fn (string $key, callable $callback): mixed => $callback($item));

        return $cache;
    }

    private function http(string ...$fixtures): MockHttpClient
    {
        $responses = [];
        foreach ($fixtures as $fixture) {
            $body = (string) file_get_contents(self::FIXTURES.$fixture);
            $responses[] = function (string $method, string $url) use ($body): MockResponse {
                $this->urls[] = $url;

                return new MockResponse($body);
            };
        }

        return new MockHttpClient($responses, 'https://www.drupal.org/');
    }
}
