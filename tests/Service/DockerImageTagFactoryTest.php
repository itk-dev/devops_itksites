<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\DockerImage;
use App\Entity\Installation;
use App\Service\DockerImageTagFactory;
use Doctrine\ORM\EntityManagerInterface;
use Hautelook\AliceBundle\PhpUnit\RefreshDatabaseTrait;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * Runs the real factory against the test database.
 */
class DockerImageTagFactoryTest extends KernelTestCase
{
    use RefreshDatabaseTrait;

    /**
     * Images are stored by their first two path segments, so
     * ghcr.io/docling-project/docling-serve-cpu and …-cu124 share one image.
     * Two of them in one result used to create it twice.
     */
    public function testImagesSharingOrganizationAndRepositoryInOneResult(): void
    {
        self::bootKernel();
        $entityManager = self::getContainer()->get(EntityManagerInterface::class);
        $installation = $entityManager->getRepository(Installation::class)->findOneBy([]);

        self::getContainer()->get(DockerImageTagFactory::class)->setDockerImageTags($installation, [
            (object) ['name' => 'cpu', 'image' => 'ghcr.io/probe-project/probe-serve-cpu', 'version' => '1.0.0'],
            (object) ['name' => 'gpu', 'image' => 'ghcr.io/probe-project/probe-serve-cu124', 'version' => '1.0.0'],
        ]);
        $entityManager->flush();

        $images = $entityManager->getRepository(DockerImage::class)->findBy(['organization' => 'ghcr.io', 'repository' => 'probe-project']);
        $this->assertCount(1, $images);
        $this->assertCount(2, $images[0]->getDockerImageTags());
    }
}
