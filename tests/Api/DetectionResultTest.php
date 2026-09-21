<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Message\PersistDetectionResult;
use App\Security\ApiKeyAuthenticator;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

/**
 * Contract of POST /api/detection_results, the only write endpoint of the API.
 *
 * The operation is declared with status 202, output: false and messenger: true.
 * App\Entity\DetectionResult is routed to the sync transport so the handler can
 * read the security context, and it dispatches PersistDetectionResult to async.
 * Nothing is persisted during the request.
 */
class DetectionResultTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * Boot the kernel with the client, as API Platform has always done. From
     * 5.0 the default becomes false; setting it explicitly keeps the fixtures
     * reachable and keeps the suite free of the 4.x deprecation.
     */
    protected static ?bool $alwaysBootKernel = true;

    private const string ENDPOINT = '/api/detection_results';

    private function server(): Server
    {
        $servers = $this->entityManager()->getRepository(Server::class)->findAll();
        self::assertNotEmpty($servers, 'The server fixtures must be loaded');

        return $servers[0];
    }

    private function entityManager(): EntityManagerInterface
    {
        $manager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }

    /**
     * @param array<string, string> $extraHeaders
     * @param array<string, string> $body
     */
    private function post(Client $client, ?string $apiKey, array $body, array $extraHeaders = []): void
    {
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ] + $extraHeaders;

        if (null !== $apiKey) {
            $headers[ApiKeyAuthenticator::AUTH_HEADER] = ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apiKey;
        }

        $client->request('POST', self::ENDPOINT, [
            'headers' => $headers,
            'body' => json_encode($body, JSON_THROW_ON_ERROR),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(string $data = '{"packages":{}}'): array
    {
        return [
            'type' => DetectionType::NGINX,
            'rootDir' => '/data/www/example-site/htdocs',
            'data' => $data,
        ];
    }

    private function asyncTransport(): InMemoryTransport
    {
        $transport = static::getContainer()->get('messenger.transport.async');
        self::assertInstanceOf(InMemoryTransport::class, $transport);

        return $transport;
    }

    public function testValidSubmissionIsAccepted(): void
    {
        $client = static::createClient();
        $this->post($client, $this->server()->getApiKey(), $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
    }

    public function testAcceptedResponseHasNoBody(): void
    {
        $client = static::createClient();
        $this->post($client, $this->server()->getApiKey(), $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        self::assertSame('', $client->getResponse()->getContent(), 'The operation declares output: false');
    }

    public function testSubmissionDispatchesPersistMessageToTheAsyncTransport(): void
    {
        $client = static::createClient();
        $this->post($client, $this->server()->getApiKey(), $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $messages = $this->asyncTransport()->getSent();
        self::assertCount(1, $messages);

        $message = $messages[0]->getMessage();
        self::assertInstanceOf(PersistDetectionResult::class, $message);
        self::assertSame($this->server()->getApiKey(), $message->serverApiKey);
        self::assertSame(DetectionType::NGINX, $message->detectionResult->getType());
    }

    public function testNothingIsPersistedDuringTheRequest(): void
    {
        $before = $this->entityManager()->getRepository(DetectionResult::class)->count([]);

        $client = static::createClient();
        $this->post($client, $this->server()->getApiKey(), $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        self::assertSame(
            $before,
            $this->entityManager()->getRepository(DetectionResult::class)->count([]),
            'Persistence happens in the async worker, not in the request'
        );
    }

    public function testDuplicateSubmissionIsAlsoAccepted(): void
    {
        $client = static::createClient();
        $payload = $this->validPayload();

        $this->post($client, $this->server()->getApiKey(), $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);
        self::assertCount(1, $this->asyncTransport()->getSent());

        // Symfony resets the in-memory transport between requests, so the
        // count starts from zero again rather than accumulating.
        $this->post($client, $this->server()->getApiKey(), $payload);
        $this->assertResponseStatusCodeSame(
            Response::HTTP_ACCEPTED,
            'Deduplication happens in the async handler, so the API accepts duplicates'
        );
        self::assertCount(
            1,
            $this->asyncTransport()->getSent(),
            'The repeat submission is queued too; the handler deduplicates it later'
        );
    }

    public function testMissingApiKeyIsRejected(): void
    {
        $client = static::createClient();
        $this->post($client, null, $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testUnknownApiKeyIsRejected(): void
    {
        $client = static::createClient();
        $this->post($client, 'not-a-real-api-key', $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testAuthorizationHeaderWithoutTheApikeyPrefixIsRejected(): void
    {
        $client = static::createClient();
        $client->request('POST', self::ENDPOINT, [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => 'Bearer '.$this->server()->getApiKey(),
            ],
            'body' => json_encode($this->validPayload(), JSON_THROW_ON_ERROR),
        ]);

        $this->assertResponseStatusCodeSame(
            Response::HTTP_UNAUTHORIZED,
            'The authenticator only supports the "Apikey " prefix'
        );
    }

    public function testRejectionLeavesTheAsyncTransportEmpty(): void
    {
        $client = static::createClient();
        $this->post($client, null, $this->validPayload());

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        self::assertCount(0, $this->asyncTransport()->getSent());
    }

    public function testMalformedJsonIsRejected(): void
    {
        $client = static::createClient();
        $client->request('POST', self::ENDPOINT, [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$this->server()->getApiKey(),
            ],
            'body' => '{"type": "nginx", not json}',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);
    }

    public function testGetIsNotAllowedOnTheCollection(): void
    {
        $client = static::createClient();
        $client->request('GET', self::ENDPOINT, [
            'headers' => [
                'accept' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$this->server()->getApiKey(),
            ],
        ]);

        $this->assertResponseStatusCodeSame(
            Response::HTTP_METHOD_NOT_ALLOWED,
            'Only the Post operation is declared'
        );
    }

    /**
     * RootDirNormalizer rewrites /home/ to /data/ and drops a trailing
     * /public, so the harvester's differing paths for one installation
     * collapse to a single key. It does not touch trailing slashes.
     */
    #[DataProvider('rootDirNormalisationCases')]
    public function testRootDirIsNormalised(string $submitted, string $expected): void
    {
        $client = static::createClient();
        $payload = $this->validPayload();
        $payload['rootDir'] = $submitted;

        $this->post($client, $this->server()->getApiKey(), $payload);
        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED);

        $message = $this->asyncTransport()->getSent()[0]->getMessage();
        self::assertInstanceOf(PersistDetectionResult::class, $message);
        self::assertSame($expected, $message->detectionResult->getRootDir());
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function rootDirNormalisationCases(): iterable
    {
        yield '/home/ becomes /data/' => [
            '/home/www/example_dk/htdocs',
            '/data/www/example_dk/htdocs',
        ];
        yield 'trailing /public is dropped' => [
            '/data/www/example_dk/htdocs/public',
            '/data/www/example_dk/htdocs',
        ];
        yield 'both rules apply together' => [
            '/home/www/example_dk/htdocs/public',
            '/data/www/example_dk/htdocs',
        ];
        yield 'an already normalised path is unchanged' => [
            '/data/www/example_dk/htdocs',
            '/data/www/example_dk/htdocs',
        ];
        yield 'a trailing slash is left alone' => [
            '/data/www/example_dk/htdocs/',
            '/data/www/example_dk/htdocs/',
        ];
    }
}
