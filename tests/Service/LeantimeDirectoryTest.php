<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\LeantimeService;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * The ticket search and the user directory speak to two different APIs on the
 * same host: JSON-RPC wants a scalar status, and the directory comes from the
 * data-api plugin because `users.getAll` is forbidden for our key.
 */
class LeantimeDirectoryTest extends TestCase
{
    public function testSendsTheOpenStatusesAsOneScalar(): void
    {
        $body = null;
        $client = new MockHttpClient(function (string $method, string $url, array $options) use (&$body): MockResponse {
            $body = json_decode((string) $options['body'], true, 512, JSON_THROW_ON_ERROR);

            return new MockResponse(json_encode(['jsonrpc' => '2.0', 'result' => []], JSON_THROW_ON_ERROR));
        });

        (new LeantimeService($client, new NullLogger()))->findOpenSecurityTickets();

        // An array here is what Leantime answers -32000 to.
        self::assertSame('1,2,3,4', $body['params']['searchCriteria']['status']);
    }

    public function testBuildsTheEmailMapByPagingTheDataApi(): void
    {
        $paths = [];
        $pages = [
            ['parameters' => ['limit' => 1], 'results' => [['id' => 7, 'name' => 'Ada Lovelace', 'email' => 'Ada@Example.dk']]],
            ['parameters' => ['limit' => 1], 'results' => [['id' => 9, 'name' => 'Grace Hopper', 'email' => 'grace@example.dk']]],
            ['parameters' => ['limit' => 1], 'results' => []],
        ];
        $client = new MockHttpClient(function (string $method, string $url) use (&$paths, &$pages): MockResponse {
            $paths[] = parse_url($url, PHP_URL_PATH);

            return new MockResponse(json_encode(array_shift($pages), JSON_THROW_ON_ERROR));
        });

        $service = new LeantimeService($client, new NullLogger());

        self::assertSame(7, $service->findUserIdByEmail('  ADA@example.dk '));
        self::assertSame(9, $service->findUserIdByEmail('grace@example.dk'));
        self::assertNull($service->findUserIdByEmail('nobody@example.dk'));

        // Case-sensitive path, and fetched once however many lookups follow.
        self::assertSame(['/APIData/API/workers', '/APIData/API/workers', '/APIData/API/workers'], $paths);
    }

    public function testRaisesTheReasonWhenTheDataApiRejectsTheRequest(): void
    {
        $client = new MockHttpClient(new MockResponse(
            json_encode(['error' => 'limit must be at least 1.'], JSON_THROW_ON_ERROR),
            ['http_code' => 400],
        ));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Leantime data-api error (400): limit must be at least 1.');

        (new LeantimeService($client, new NullLogger()))->findUserIdByEmail('ada@example.dk');
    }
}
