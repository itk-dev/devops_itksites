<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Component\HttpFoundation\Response;

class DeployResultTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    private const string BODY = '{
        "serverName": "example.itkdev.dk",
        "rootDir": "/data/www/example/htdocs",
        "repoUrl": "https://github.com/itk-dev/example.git",
        "tag": "1.4.2"
    }';

    public function testUnauthenticatedRequestsAreDenied(): void
    {
        $this::$alwaysBootKernel = false;

        static::createClient()->request('POST', '/api/deploy_results', [
            'headers' => $this->headers(),
            'body' => self::BODY,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED, 'Unauthenticated requests should be denied');
    }

    /**
     * A server may report what is installed on it, but not that something was
     * deployed somewhere. The deploy key is scoped to this one endpoint.
     */
    public function testServerKeysAreDenied(): void
    {
        $client = static::createClient();

        $em = $this->getContainer()->get('doctrine')->getManager();
        $servers = $em->getRepository(Server::class)->findAll();

        $client->request('POST', '/api/deploy_results', [
            'headers' => $this->headers($servers[0]->getApiKey()),
            'body' => self::BODY,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'Server keys should not be allowed to report deployments');
    }

    public function testTheDeployKeyIsAccepted(): void
    {
        static::createClient()->request('POST', '/api/deploy_results', [
            'headers' => $this->headers('w00dp3ck3rt3stk3y'),
            'body' => self::BODY,
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_ACCEPTED, 'The deploy key should be accepted');
    }

    /**
     * The deploy key is scoped: it unlocks deploy reporting and nothing else.
     */
    public function testTheDeployKeyCannotReadTheApi(): void
    {
        static::createClient()->request('GET', '/api/servers', [
            'headers' => $this->headers('w00dp3ck3rt3stk3y'),
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN, 'The deploy key should not be able to read servers');
    }

    public function testAMissingTagIsRejected(): void
    {
        static::createClient()->request('POST', '/api/deploy_results', [
            'headers' => $this->headers('w00dp3ck3rt3stk3y'),
            'body' => '{
                "serverName": "example.itkdev.dk",
                "rootDir": "/data/www/example/htdocs",
                "repoUrl": "https://github.com/itk-dev/example.git",
                "tag": ""
            }',
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY, 'A blank tag should fail validation');
    }

    /**
     * @return array<string, string>
     */
    private function headers(?string $apiKey = null): array
    {
        $headers = [
            'accept' => 'application/json',
            'content-type' => 'application/json',
        ];

        if (null !== $apiKey) {
            $headers[ApiKeyAuthenticator::AUTH_HEADER] = ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apiKey;
        }

        return $headers;
    }
}
