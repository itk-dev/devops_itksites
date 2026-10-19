<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\DockerImage;
use App\Entity\Installation;
use App\Service\DockerImageTagFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factory against the test database.
 */
class DockerImageTagFactoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    private EntityManagerInterface $entityManager;
    private Installation $installation;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $this->installation = $this->entityManager->getRepository(Installation::class)->findOneBy([]);
    }

    /**
     * @param array{string, string, string} $expected registry, organization and repository
     */
    #[DataProvider('imageProvider')]
    public function testSplitsImageName(string $image, array $expected): void
    {
        $this->images(['probe' => $image]);

        $this->assertCount(1, $this->entityManager->getRepository(DockerImage::class)->findBy(array_combine(['registry', 'organization', 'repository'], $expected)));
    }

    /**
     * @return iterable<string, array{string, array{string, string, string}}>
     */
    public static function imageProvider(): iterable
    {
        yield 'docker hub official image' => ['probe-redis', ['docker.io', 'probe-redis', '']];
        yield 'docker hub' => ['probe-itkdev/php8.4-fpm', ['docker.io', 'probe-itkdev', 'php8.4-fpm']];
        yield 'registry' => ['ghcr.io/probe-project/probe-serve', ['ghcr.io', 'probe-project', 'probe-serve']];
        yield 'registry, nested repository' => ['ghcr.io/probe-project/team/probe-serve', ['ghcr.io', 'probe-project', 'team/probe-serve']];
        yield 'registry with port' => ['registry:5000/probe-team/app', ['registry:5000', 'probe-team', 'app']];
        yield 'localhost' => ['localhost/probe-app', ['localhost', 'probe-app', '']];
    }

    /**
     * ghcr.io/docling-project/docling-serve-cpu and …-cu124 in one result used
     * to collapse to ghcr.io/docling-project and create that image twice.
     */
    public function testImagesUnderOneOrganizationInOneResult(): void
    {
        $this->images([
            'cpu' => 'ghcr.io/probe-project/probe-serve-cpu',
            'gpu' => 'ghcr.io/probe-project/probe-serve-cu124',
        ]);

        $images = $this->entityManager->getRepository(DockerImage::class)->findBy(['registry' => 'ghcr.io', 'organization' => 'probe-project']);
        $this->assertCount(2, $images);
    }

    public function testNamesIncludeTheRegistry(): void
    {
        $this->images(['hub' => 'probe-itkdev/php8.4-fpm', 'ghcr' => 'ghcr.io/probe-project/probe-serve']);

        $images = $this->entityManager->getRepository(DockerImage::class);
        $hub = $images->findOneBy(['organization' => 'probe-itkdev']);
        $this->assertSame('docker.io/probe-itkdev/php8.4-fpm', (string) $hub);
        $this->assertSame('docker.io/probe-itkdev/php8.4-fpm:1.0.0', (string) $hub->getDockerImageTags()->first());
        $this->assertSame('ghcr.io/probe-project/probe-serve', (string) $images->findOneBy(['organization' => 'probe-project']));
    }

    /**
     * A tag is an image:tag in use; what the Compose services running it are
     * called does not matter.
     */
    public function testSameImageTwiceInOneResult(): void
    {
        $this->images([
            'phpfpm' => 'probe-itkdev/php8.4-fpm',
            'cron' => 'probe-itkdev/php8.4-fpm',
        ]);

        $images = $this->entityManager->getRepository(DockerImage::class)->findBy(['organization' => 'probe-itkdev']);
        $this->assertCount(1, $images);
        $this->assertCount(1, $images[0]->getDockerImageTags());
        $this->assertCount(1, $this->installation->getDockerImageTags());
    }

    public function testSameTagUnderOtherServiceNames(): void
    {
        $this->images(['phpfpm' => 'probe-itkdev/php8.4-fpm']);
        $this->images(['php' => 'probe-itkdev/php8.4-fpm']);

        $image = $this->entityManager->getRepository(DockerImage::class)->findOneBy(['organization' => 'probe-itkdev']);
        $this->assertCount(1, $image->getDockerImageTags());
    }

    /**
     * @param array<string, string> $images container name => image
     */
    private function images(array $images): void
    {
        $containers = [];
        foreach ($images as $name => $image) {
            $containers[] = (object) ['name' => $name, 'image' => $image, 'version' => '1.0.0'];
        }

        self::getContainer()->get(DockerImageTagFactory::class)->setDockerImageTags($this->installation, $containers);
        $this->entityManager->flush();
    }
}
