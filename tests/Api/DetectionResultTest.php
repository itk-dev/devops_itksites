<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\DetectionResult;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Transport\InMemoryTransport;

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

        // TODO update tests for async handling
        /* @var InMemoryTransport $transport */
        //        $transport = $this->getContainer()->get('messenger.transport.async_priority_normal');
        //        $this->assertCount(1, $transport->getSent());
    }

    public function testNoDuplicatesForSameHash(): void
    {
        // TODO update tests for async handling
        //        $client = static::createClient();
        //        $client->disableReboot();
        //
        //        $em = $this->getContainer()->get('doctrine')->getManager();
        //        $servers = $em->getRepository(Server::class)->findAll();
        //        $apikey = $servers[0]->getApiKey();
        //
        //        $results = $em->getRepository(DetectionResult::class)->findAll();
        //        $numberOfExistingDetectionResults = count($results);
        //
        //        $response = $client->request('POST', '/api/detection_results', [
        //            'headers' => [
        //                'content-type' => 'application/json',
        //                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
        //            ],
        //            'body' => '{
        //                          "type": "string",
        //                          "rootDir": "string",
        //                          "data": "string"
        //                        }',
        //        ]);
        //
        //        $response = $client->request('POST', '/api/detection_results', [
        //            'headers' => [
        //                'content-type' => 'application/json',
        //                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
        //            ],
        //            'body' => '{
        //                          "type": "string1",
        //                          "rootDir": "string1",
        //                          "data": "string1"
        //                        }',
        //        ]);
        //
        //        $response = $client->request('POST', '/api/detection_results', [
        //            'headers' => [
        //                'content-type' => 'application/json',
        //                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apikey,
        //            ],
        //            'body' => '{
        //                          "type": "string",
        //                          "rootDir": "string",
        //                          "data": "string"
        //                        }',
        //        ]);
        //
        //        $results = $em->getRepository(DetectionResult::class)->findAll();

        //        $this->assertCount($numberOfExistingDetectionResults + 2, $results, 'Identical POST\s should not write more rows to the DB');
    }
}
