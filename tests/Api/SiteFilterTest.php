<?php

declare(strict_types=1);

namespace App\Tests\Api;

use ApiPlatform\Test\ApiTestCase;
use ApiPlatform\Test\Client;
use App\Entity\Server;
use App\Entity\Site;
use App\Security\ApiKeyAuthenticator;
use App\Types\HostingProviderType;
use App\Types\ServerTypeType;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * The /api/sites filters mirror the EasyAdmin site filters.
 */
class SiteFilterTest extends ApiTestCase
{
    use RefreshDatabaseTrait;

    protected static ?bool $alwaysBootKernel = true;

    /**
     * Give the two fixture sites their own server and known values, so every
     * filter has a predictable result.
     */
    private function createClientWithKnownSites(): Client
    {
        $client = static::createClient();
        $manager = static::getContainer()->get('doctrine')->getManager();
        self::assertInstanceOf(EntityManagerInterface::class, $manager);

        $sites = [
            'dev.example.com' => ['alpha.example.com', ServerTypeType::STG, HostingProviderType::AZURE, '8.1.2', '/etc/nginx/sites-enabled/dev.conf'],
            'prod.example.com' => ['beta.example.com', ServerTypeType::PROD, HostingProviderType::HETZNER, '8.3', '/etc/nginx/sites-enabled/prod.conf'],
        ];
        $servers = $manager->getRepository(Server::class)->findAll();

        foreach ($manager->getRepository(Site::class)->findAll() as $site) {
            [$name, $type, $hostingProvider, $phpVersion, $configFilePath] = $sites[$site->getPrimaryDomain()];
            $server = array_shift($servers);
            self::assertInstanceOf(Server::class, $server);

            $server->setName($name)->setType($type)->setHostingProvider($hostingProvider);
            $site->setServer($server);
            $site->setPhpVersion($phpVersion)->setConfigFilePath($configFilePath);
        }
        $manager->flush();

        $client->setDefaultOptions(['headers' => [
            ApiKeyAuthenticator::AUTH_HEADER => ApiKeyAuthenticator::AUTH_HEADER_PREFIX.$servers[0]->getApiKey(),
            'accept' => 'application/json',
        ]]);

        return $client;
    }

    /**
     * @return iterable<string, array{string, list<string>}>
     */
    public static function filters(): iterable
    {
        yield 'no filter' => ['', ['dev.example.com', 'prod.example.com']];
        yield 'primary domain contains' => ['primaryDomain=prod', ['prod.example.com']];
        yield 'config file path contains' => ['configFilePath=dev.conf', ['dev.example.com']];
        yield 'php version equals' => ['phpVersion=8.3.0', ['prod.example.com']];
        yield 'php version gte' => ['phpVersion[gte]=8.2', ['prod.example.com']];
        yield 'php version lt' => ['phpVersion[lt]=8.2', ['dev.example.com']];
        yield 'php version ne' => ['phpVersion[ne]=8.3', ['dev.example.com']];
        yield 'php version range' => ['phpVersion[gt]=8.1.2&phpVersion[lte]=8.3', ['prod.example.com']];
        yield 'php version between' => ['phpVersion[between]=8.3..8.1', ['dev.example.com', 'prod.example.com']];
        yield 'php version not a version' => ['phpVersion=latest', []];
        yield 'server name' => ['server=alpha.example.com', ['dev.example.com']];
        yield 'server type' => ['serverType=prod', ['prod.example.com']];
        yield 'hosting provider' => ['hostingProvider=Azure', ['dev.example.com']];
        yield 'combined' => ['serverType=stg&hostingProvider=Hetzner', []];
    }

    /**
     * @param list<string> $expected primary domains of the matching sites
     */
    #[DataProvider('filters')]
    public function testFilter(string $query, array $expected): void
    {
        $response = $this->createClientWithKnownSites()->request('GET', '/api/sites?'.$query);

        $this->assertResponseIsSuccessful();
        $domains = array_column($response->toArray(), 'Primary domain');
        sort($domains);
        $this->assertSame($expected, $domains);
    }

    public function testUnknownServerTypeIsRejected(): void
    {
        $this->createClientWithKnownSites()->request('GET', '/api/sites?serverType=staging');

        $this->assertResponseStatusCodeSame(422);
    }
}
