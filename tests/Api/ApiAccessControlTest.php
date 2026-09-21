<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Symfony\Bundle\Test\ApiTestCase;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

/**
 * Access control of the API as a whole.
 *
 * Everything under /api requires ROLE_SERVER or ROLE_USER through
 * security.access_control, except the documentation. This matters because it
 * is the only thing guarding the GET item route API Platform generates for
 * DetectionResult, which the entity itself never declares and therefore
 * carries no security attribute of its own.
 */
class ApiAccessControlTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    /**
     * Boot the kernel with the client, as API Platform has always done. From
     * 5.0 the default becomes false; setting it explicitly keeps the fixtures
     * reachable and keeps the suite free of the 4.x deprecation.
     */
    protected static ?bool $alwaysBootKernel = true;

    private function entityManager(): EntityManagerInterface
    {
        $manager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        return $manager;
    }

    private function apiKey(): string
    {
        $servers = $this->entityManager()->getRepository(Server::class)->findAll();
        self::assertNotEmpty($servers, 'The server fixtures must be loaded');

        return (string) $servers[0]->getApiKey();
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function protectedEndpoints(): iterable
    {
        yield 'server collection' => ['/api/servers'];
        yield 'site collection' => ['/api/sites'];
        yield 'detection result item' => ['/api/detection_results/01JQZZZZZZZZZZZZZZZZZZZZZZ'];
    }

    #[DataProvider('protectedEndpoints')]
    public function testEndpointRequiresAuthentication(string $path): void
    {
        static::createClient()->request('GET', $path, [
            'headers' => ['accept' => 'application/json'],
        ]);

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * The generated GET item route for DetectionResult has no security
     * attribute; access_control is what stops it being public.
     */
    public function testGeneratedDetectionResultItemRouteIsNotPublic(): void
    {
        static::createClient()->request('GET', '/api/detection_results/01JQZZZZZZZZZZZZZZZZZZZZZZ', [
            'headers' => ['accept' => 'application/json'],
        ]);

        $this->assertResponseStatusCodeSame(
            Response::HTTP_UNAUTHORIZED,
            'DetectionResult declares only a Post operation, so this route is guarded by access_control alone'
        );
    }

    public function testDocumentationIsPublic(): void
    {
        static::createClient()->request('GET', '/api/docs', [
            'headers' => ['accept' => 'text/html'],
        ]);

        $this->assertResponseIsSuccessful();
    }

    /**
     * Server::ROLES is ['ROLE_USER', 'ROLE_SERVER'], so a harvester key also
     * satisfies the ROLE_USER requirement on the read resources and can list
     * servers. That is deliberate, and safe only because the export group is
     * limited to the name.
     */
    public function testServerKeyCanListServersButSeesNoSecrets(): void
    {
        $client = static::createClient();
        $client->request('GET', '/api/servers', [
            'headers' => [
                'accept' => 'application/json',
                ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$this->apiKey(),
            ],
        ]);

        $this->assertResponseIsSuccessful();

        $payload = $client->getResponse()->toArray();
        self::assertNotEmpty($payload);

        foreach ($payload as $server) {
            self::assertSame(
                ['Name'],
                array_keys($server),
                'The export group must stay limited to the name; it must never carry the API key'
            );
        }
    }

    /**
     * Errors are rendered as JSON for every format the API serves. The default
     * error_formats map application/ld+json to a jsonld format this project
     * does not register, which turned every such error into a 500.
     */
    #[DataProvider('errorContentTypes')]
    public function testErrorsAreRenderedForEveryAcceptedContentType(string $accept): void
    {
        static::createClient()->request('GET', '/api/servers', [
            'headers' => ['accept' => $accept],
        ]);

        $this->assertResponseStatusCodeSame(
            Response::HTTP_UNAUTHORIZED,
            sprintf('An error with Accept: %s must keep its status code', $accept)
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function errorContentTypes(): iterable
    {
        yield 'json' => ['application/json'];
        yield 'ld+json' => ['application/ld+json'];
        yield 'problem+json' => ['application/problem+json'];
    }
}
