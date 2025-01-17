<?php

declare(strict_types=1);

namespace App\Tests\MessageHandler;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Message\PersistDetectionResult;
use App\Security\ApiKeyAuthenticator;
use Symfony\Component\Messenger\Transport\InMemory\InMemoryTransport;

class DetectionResultHandlerTest extends ApiTestCase
{
    public function testMessageDispatchOnPost(): void
    {
        $client = static::createClient();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $servers = $em->getRepository(Server::class)->findAll();
        $apikey = $servers[0]->getApiKey();

        $response = $client->request('POST', '/api/detection_results', [
            'headers' => [
                'accept' => 'application/json',
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
            ],
            'body' => '{
                "type": "string",
                "rootDir": "string",
                "data": "string"
            }',
        ]);

        /** @var InMemoryTransport $transportAsync */
        $transportAsync = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(1, $transportAsync->getSent());

        $async = $transportAsync->get();
        /** @var PersistDetectionResult $message */
        $message = $async[0]->getMessage();

        $this->assertInstanceOf(DetectionResult::class, $message->detectionResult);
        $this->assertEquals($apikey, $message->serverApiKey);
        $this->assertInstanceOf(\DateTimeImmutable::class, $message->receivedAt);

        $this->assertEqualsWithDelta(\time(), $message->receivedAt->getTimestamp(), 1.0);
    }
}
