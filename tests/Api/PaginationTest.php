<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use App\Entity\Server;
use App\Security\ApiKeyAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;

/**
 * Collections are paginated by the global defaults: 100 items per page, with
 * the Hydra metadata in JSON-LD, and ?pagination=false to turn it off.
 */
class PaginationTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected static ?bool $alwaysBootKernel = true;

    /**
     * Top the ten fixture servers up to 101, one more than a page.
     */
    private function createClientWith101Servers(): Client
    {
        $client = static::createClient();
        $manager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        for ($i = 0; $i < 91; ++$i) {
            $manager->persist(new Server()
                ->setName('server-'.$i.'.example.com')
                ->setSystem('Ubuntu 24.04')
                ->setType('stg')
                ->setHostingProvider('Azure'));
        }
        $manager->flush();

        $apiKey = $manager->getRepository(Server::class)->findOneBy([])?->getApiKey();
        $client->setDefaultOptions(['headers' => [
            ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$apiKey,
        ]]);

        return $client;
    }

    public function testCollectionIsPaginated(): void
    {
        $response = $this->createClientWith101Servers()->request('GET', '/api/servers', [
            'headers' => ['accept' => 'application/ld+json'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@id' => '/api/servers',
            '@type' => 'Collection',
            'totalItems' => 101,
            'view' => [
                '@id' => '/api/servers?page=1',
                '@type' => 'PartialCollectionView',
                'first' => '/api/servers?page=1',
                'last' => '/api/servers?page=2',
                'next' => '/api/servers?page=2',
            ],
        ]);
        $this->assertCount(100, $response->toArray()['member']);
        $this->assertMatchesResourceCollectionJsonSchema(Server::class);
    }

    public function testClientCanDisablePagination(): void
    {
        $response = $this->createClientWith101Servers()->request('GET', '/api/servers?pagination=false', [
            'headers' => ['accept' => 'application/ld+json'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains(['totalItems' => 101]);
        $this->assertCount(101, $response->toArray()['member']);
        $this->assertArrayNotHasKey('view', $response->toArray());
    }

    public function testPlainJsonIsAPageOfItems(): void
    {
        $response = $this->createClientWith101Servers()->request('GET', '/api/servers', [
            'headers' => ['accept' => 'application/json'],
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertCount(100, $response->toArray());
    }
}
