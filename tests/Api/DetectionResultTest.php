<?php

namespace App\Tests\Api;

use ApiPlatform\Core\Bridge\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class DetectionResultTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    public function testAuthenticationDenied(): void
    {
        $response = static::createClient()->request('POST', '/api/detection_results', [
            'body' => '{
                          "type": "string",
                          "rootDir": "string",
                          "data": [
                            "string"
                          ]
                        }',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Unauthenticated requests should be denied');
    }

    public function testAuthenticationOk(): void
    {
        $client = static::createClient();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $servers = $em->getRepository(Server::class)->findAll();
        $apikey = $servers[0]->getApiKey();

        $results = $em->getRepository(DetectionResult::class)->findAll();
        $beforeCount = \count($results);

        $response = $client->request('POST', '/api/detection_results', [
            'headers' => [
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
            ],
            'body' => '{
                "type": "string",
                "rootDir": "string",
                "data": "string"
            }',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED, 'Authenticated requests should be accepted');

        $transport = $this->getContainer()->get('messenger.transport.async');
        $this->assertCount(1, $transport->getSent());

        $results = $em->getRepository(DetectionResult::class)->findAll();
        $this->assertCount($beforeCount + 1, $results);
    }

    public function testNoDuplicatesForSameHash(): void
    {
        $client = static::createClient();
        $client->disableReboot();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $servers = $em->getRepository(Server::class)->findAll();
        $apikey = $servers[0]->getApiKey();

        $results = $em->getRepository(DetectionResult::class)->findAll();
        $beforeCount = \count($results);

        $response = $client->request('POST', '/api/detection_results', [
            'headers' => [
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
            ],
            'body' => '{
                          "type": "string",
                          "rootDir": "string",
                          "data": "string"
                        }',
        ]);

        $response = $client->request('POST', '/api/detection_results', [
            'headers' => [
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
            ],
            'body' => '{
                          "type": "string1",
                          "rootDir": "string1",
                          "data": "string1"
                        }',
        ]);

        $response = $client->request('POST', '/api/detection_results', [
            'headers' => [
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
            ],
            'body' => '{
                          "type": "string",
                          "rootDir": "string",
                          "data": "string"
                        }',
        ]);

        $results = $em->getRepository(DetectionResult::class)->findAll();

        $this->assertCount($beforeCount + 2, $results, 'Identical POST\s should not write more rows to the DB');
    }
}
