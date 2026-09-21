<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\LeantimeService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Contracts\Service\ResetInterface;

/**
 * The user directory is memoised, which is the point of it — resolveUserName()
 * runs inside a loop over tickets, so the cache saves one API round trip per
 * ticket. That makes the cache worth keeping and the lifetime worth managing:
 * under php-fpm the instance died with the request, in a worker it does not.
 *
 * These tests fix both ends of the contract: the fetch happens once, and
 * reset() puts the service back to where it started.
 */
#[CoversClass(LeantimeService::class)]
class LeantimeServiceResetTest extends TestCase
{
    public function testItIsResettable(): void
    {
        $this->assertInstanceOf(
            ResetInterface::class,
            new LeantimeService(new MockHttpClient()),
            'autoconfigure only tags kernel.reset when the service implements ResetInterface'
        );
    }

    public function testTheDirectoryIsFetchedOncePerInstance(): void
    {
        $client = new MockHttpClient([self::directory(), self::directory()]);
        $service = new LeantimeService($client);

        $this->assertSame(7, $service->findUserIdByEmail('someone@aarhus.dk'));
        $this->assertSame(7, $service->findUserIdByEmail('someone@aarhus.dk'));

        $this->assertSame(1, $client->getRequestsCount(), 'the second lookup must come from the cache');
    }

    public function testResetForcesTheDirectoryToBeFetchedAgain(): void
    {
        $client = new MockHttpClient([self::directory(), self::directory()]);
        $service = new LeantimeService($client);

        $service->findUserIdByEmail('someone@aarhus.dk');
        $service->reset();
        $service->findUserIdByEmail('someone@aarhus.dk');

        $this->assertSame(2, $client->getRequestsCount(), 'without this a worker would never see directory changes');
    }

    public function testTheDirectoryIsRereadAfterReset(): void
    {
        $client = new MockHttpClient([
            self::directory(),
            new JsonMockResponse(['result' => [
                ['id' => 9, 'firstname' => 'New', 'lastname' => 'Starter', 'email' => 'new.starter@aarhus.dk'],
            ]]),
        ]);
        $service = new LeantimeService($client);

        $this->assertNull($service->findUserIdByEmail('new.starter@aarhus.dk'), 'not in the directory yet');

        $service->reset();

        $this->assertSame(9, $service->findUserIdByEmail('new.starter@aarhus.dk'), 'visible after the reset');
    }

    private static function directory(): JsonMockResponse
    {
        return new JsonMockResponse(['result' => [
            ['id' => 7, 'firstname' => 'Some', 'lastname' => 'One', 'email' => 'someone@aarhus.dk'],
        ]]);
    }
}
