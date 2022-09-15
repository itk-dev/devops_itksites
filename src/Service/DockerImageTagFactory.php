<?php

namespace App\Service;

use App\Entity\DockerImage;
use App\Entity\DockerImageTag;
use App\Entity\Installation;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;

class DockerImageTagFactory
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    public function setDockerImageTags(Installation $installation, array $dockerImages): void
    {
        $dockerImageRepository = $this->entityManager->getRepository(DockerImage::class);
        $dockerImageTagRepository = $this->entityManager->getRepository(DockerImageTag::class);

        $dockerImageTags = new ArrayCollection();
        foreach ($dockerImages as $image) {
            $parts = explode('/', $image->image);
            $organization = $parts[0] ?? '';
            $repository = $parts[1] ?? '';

            $dockerImage = $dockerImageRepository->findOneBy([
                'organization' => $organization,
                'repository' => $repository,
            ]);

            if (null === $dockerImage) {
                $dockerImage = new DockerImage();
                $dockerImage->setOrganization($organization);
                $dockerImage->setRepository($repository);

                $this->entityManager->persist($dockerImage);
            }

            $tag = $image->version ?? '';

            $dockerImageTag = $dockerImageTagRepository->findOneBy([
                'dockerImage' => $dockerImage,
                'name' => $image->name,
                'tag' => $tag,
            ]);

            if (null === $dockerImageTag) {
                $dockerImageTag = new DockerImageTag();
                $dockerImage->addDockerImageTag($dockerImageTag);
                $installation->addDockerImageTag($dockerImageTag);

                $this->entityManager->persist($dockerImageTag);
            }

            $dockerImageTag->setName($image->name);
            $dockerImageTag->setTag($tag);

            $dockerImageTags->add($dockerImageTag);
            $this->entityManager->flush();
        }

        $installation->setDockerImageTags($dockerImageTags);

        $this->entityManager->flush();
    }
}
