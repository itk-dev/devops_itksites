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
        $dockerImageTags = new ArrayCollection();
        $images = [];
        foreach ($containers as $container) {
            $parts = explode('/', $container->image);
            $organization = $parts[0] ?? '';
            $repository = $parts[1] ?? '';

            $dockerImage = array_key_exists($container->image, $images) ? $images[$container->image] : null;

            if (null === $dockerImage) {
                $dockerImage = $this->dockerImageRepository->findOneBy([
                    'organization' => $organization,
                    'repository' => $repository,
                ]);

                if (null === $dockerImage) {
                    $dockerImage = new DockerImage();
                    $this->entityManager->persist($dockerImage);

                    $dockerImage->setOrganization($organization);
                    $dockerImage->setRepository($repository);
                }

                $images[$container->image] = $dockerImage;
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
}
