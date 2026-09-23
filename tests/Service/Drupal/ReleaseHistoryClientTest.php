<?php

declare(strict_types=1);

namespace App\Tests\Service\Drupal;

use App\Service\Drupal\ReleaseHistoryClient;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class ReleaseHistoryClientTest extends TestCase
{
    private const string FIXTURES = __DIR__.'/../../fixtures/drupal/';

    private string $lastUrl = '';

    public function testFetchParsesReleases(): void
    {
        $http = $this->http('release-history-key_auth.xml');
        $history = new ReleaseHistoryClient($http, new ArrayAdapter())->fetch('key_auth');

        self::assertSame('https://updates.drupal.org/release-history/key_auth/current', $this->lastUrl);
        self::assertNotNull($history);

        $fixed = $history->get('2.2.4');
        self::assertNotNull($fixed);
        self::assertSame(['Security update'], $fixed->terms);
        self::assertTrue($fixed->security);
        self::assertFalse($fixed->insecure);

        $installed = $history->get('2.2.0');
        self::assertNotNull($installed);
        self::assertTrue($installed->insecure);
        self::assertFalse($installed->security);

        // Legacy versions are normalised; releases without terms have none.
        $legacy = $history->get('1.1.0');
        self::assertNotNull($legacy);
        self::assertSame([], $legacy->terms);
        self::assertNotNull($history->get('1.0.0-beta1'));
        self::assertNull($history->get('8.x-1.1'));
    }

    public function testFetchSkipsReleasesWithoutVersion(): void
    {
        $body = '<project><releases><release><version></version></release><release><version>2.2.4</version></release></releases></project>';
        $history = new ReleaseHistoryClient(new MockHttpClient(new MockResponse($body)), new ArrayAdapter())->fetch('key_auth');

        self::assertNotNull($history);
        self::assertSame(['2.2.4'], array_keys($history->releases));
    }

    public function testFetchReturnsNullForNonProject(): void
    {
        $client = new ReleaseHistoryClient($this->http('release-history-error.xml'), new ArrayAdapter());

        self::assertNull($client->fetch('not_a_real_project_8406'));
    }

    public function testFetchThrowsOnInvalidXml(): void
    {
        $client = new ReleaseHistoryClient(new MockHttpClient(new MockResponse('<html>')), new ArrayAdapter());

        $this->expectException(\UnexpectedValueException::class);
        $client->fetch('key_auth');
    }

    public function testFetchIsCached(): void
    {
        $cache = new ArrayAdapter();
        $http = $this->http('release-history-key_auth.xml');
        $client = new ReleaseHistoryClient($http, $cache);

        $client->fetch('key_auth');
        self::assertNotNull($client->fetch('key_auth'));
        self::assertSame(1, $http->getRequestsCount());
    }

    public function testNonProjectIsCached(): void
    {
        $http = $this->http('release-history-error.xml');
        $client = new ReleaseHistoryClient($http, new ArrayAdapter());

        $client->fetch('not_a_real_project_8406');
        self::assertNull($client->fetch('not_a_real_project_8406'));
        self::assertSame(1, $http->getRequestsCount());
    }

    #[DataProvider('ttlProvider')]
    public function testCacheTtl(string $fixture, int $ttl): void
    {
        $client = new ReleaseHistoryClient($this->http($fixture), $this->expectTtl($ttl));

        $client->fetch('key_auth');
    }

    /**
     * @return iterable<string, array{string, int}>
     */
    public static function ttlProvider(): iterable
    {
        yield 'project' => ['release-history-key_auth.xml', 12 * 3600];
        yield 'not a project' => ['release-history-error.xml', 7 * 86400];
    }

    private function expectTtl(int $ttl): CacheInterface
    {
        $item = $this->createMock(ItemInterface::class);
        $item->expects(self::once())->method('expiresAfter')->with($ttl);
        $cache = $this->createStub(CacheInterface::class);
        $cache->method('get')->willReturnCallback(static fn (string $key, callable $callback): mixed => $callback($item));

        return $cache;
    }

    private function http(string $fixture): MockHttpClient
    {
        $body = (string) file_get_contents(self::FIXTURES.$fixture);

        return new MockHttpClient(function (string $method, string $url) use ($body): MockResponse {
            $this->lastUrl = $url;

            return new MockResponse($body);
        }, 'https://updates.drupal.org/');
    }
}
