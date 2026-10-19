<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\DockerImage;
use App\Entity\DockerImageTag;
use App\Entity\Installation;
use App\Repository\DockerImageRepository;
use App\Repository\DockerImageTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class DockerImageTagFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DockerImageRepository $dockerImageRepository,
        private readonly DockerImageTagRepository $dockerImageTagRepository,
    ) {
    }

    public function setDockerImageTags(Installation $installation, array $containers): void
    {
        // Some detection results have duplicated containers
        $containersKeyed = [];
        foreach ($containers as $container) {
            $containersKeyed[$container->name] = $container;
        }

        $dockerImageTags = new ArrayCollection();
        $images = [];
        foreach ($containersKeyed as $container) {
            [$registry, $organization, $repository] = $this->parseImage((string) $container->image);
            // Keyed like the unique constraint and the lookup below.
            $imageKey = $registry.'/'.$organization.'/'.$repository;

            $dockerImage = $images[$imageKey] ?? null;

            if (null === $dockerImage) {
                $dockerImage = $this->dockerImageRepository->findOneBy([
                    'registry' => $registry,
                    'organization' => $organization,
                    'repository' => $repository,
                ]);

                if (null === $dockerImage) {
                    $dockerImage = new DockerImage();
                    $this->entityManager->persist($dockerImage);

                    $dockerImage->setRegistry($registry);
                    $dockerImage->setOrganization($organization);
                    $dockerImage->setRepository($repository);
                }

                $images[$imageKey] = $dockerImage;
            }

            $tag = $container->version ?? '';

            $dockerImageTag = $this->dockerImageTagRepository->findOneBy([
                'dockerImage' => $dockerImage,
                'name' => $container->name,
                'tag' => $tag,
            ]);

            if (null === $dockerImageTag) {
                $dockerImageTag = new DockerImageTag();
                $this->entityManager->persist($dockerImageTag);

                $dockerImage->addDockerImageTag($dockerImageTag);
                $installation->addDockerImageTag($dockerImageTag);
            }

            $dockerImageTag->setName($container->name);
            $dockerImageTag->setTag($tag);

            $dockerImageTags->add($dockerImageTag);
        }

        $installation->setDockerImageTags($dockerImageTags);
    }

    /**
     * Registry, organization and repository, by Docker's rule: the first
     * segment names a registry when it has a "." or ":" or is "localhost",
     * otherwise the image is on Docker Hub.
     *
     * @return array{string, string, string}
     */
    private function parseImage(string $image): array
    {
        $parts = explode('/', $image);
        $registry = DockerImage::DOCKER_HUB;
        if (count($parts) > 1 && (str_contains($parts[0], '.') || str_contains($parts[0], ':') || 'localhost' === $parts[0])) {
            $registry = array_shift($parts);
        }
        $organization = array_shift($parts);

        return [$registry, $organization, implode('/', $parts)];
    }
}
