<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Service\LeantimeService;
use PHPUnit\Framework\TestCase;
use Psr\Log\AbstractLogger;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Callers turn a Leantime failure into a flash message and record nothing
 * else, so the log is the only place a broken integration leaves a trace —
 * and `error.data` is the only field separating a rejected key from a bad
 * method name.
 */
class LeantimeErrorLoggingTest extends TestCase
{
    public function testLogsTheErrorDataAnApiErrorCarries(): void
    {
        $logger = $this->logger();
        $service = new LeantimeService(new MockHttpClient(new MockResponse(json_encode([
            'jsonrpc' => '2.0',
            'id' => '1',
            'error' => [
                'code' => -32000,
                'message' => 'Server error',
                'data' => 'Unauthorised: API key not recognised',
            ],
        ], JSON_THROW_ON_ERROR))), $logger);

        try {
            $service->findOpenSecurityTickets();
            self::fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            self::assertSame('Leantime API error (-32000): Server error', $e->getMessage());
        }

        self::assertCount(1, $logger->records);
        [$message, $context] = $logger->records[0];
        self::assertSame('Leantime API error', $message);
        self::assertSame('Unauthorised: API key not recognised', $context['data']);
        self::assertSame('leantime.rpc.tickets.getAll', $context['method']);
        self::assertSame(-32000, $context['code']);
    }

    public function testLogsATransportFailure(): void
    {
        $logger = $this->logger();
        $service = new LeantimeService(
            new MockHttpClient(static function (): never {
                throw new \Symfony\Component\HttpClient\Exception\TransportException('Name resolution failed');
            }),
            $logger,
        );

        try {
            $service->findOpenSecurityTickets();
            self::fail('Expected a RuntimeException.');
        } catch (\RuntimeException $e) {
            self::assertStringStartsWith('Leantime request failed:', $e->getMessage());
        }

        self::assertCount(1, $logger->records);
        self::assertSame('Leantime request failed', $logger->records[0][0]);
    }

    public function testDoesNotFatalOnAMalformedErrorField(): void
    {
        $logger = $this->logger();
        $service = new LeantimeService(new MockHttpClient(new MockResponse(json_encode([
            'jsonrpc' => '2.0',
            'error' => 'Server error',
        ], JSON_THROW_ON_ERROR))), $logger);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Leantime API error (?): Server error');

        $service->findOpenSecurityTickets();
    }

    /**
     * @return AbstractLogger&object{records: list<array{string, array<string, mixed>}>}
     */
    private function logger(): AbstractLogger
    {
        return new class extends AbstractLogger {
            /** @var list<array{string, array<string, mixed>}> */
            public array $records = [];

            public function log($level, $message, array $context = []): void
            {
                $this->records[] = [(string) $message, $context];
            }
        };
    }
}
