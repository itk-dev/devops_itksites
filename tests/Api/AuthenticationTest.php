<?php

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationTest extends ApiTestCase
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

        $response = static::createClient()->request('POST', '/api/detection_results', [
            'headers' => [
                'content-type' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.'123456789',
            ],
            'body' => '{
                "type": "string",
                "rootDir": "string",
                "data": "string"
            }',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Requests with invalid api key should be denied');
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
    }
}
