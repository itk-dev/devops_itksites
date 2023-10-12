<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DockerImageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DockerImageRepository::class)]
#[ORM\UniqueConstraint(name: 'organization_repository', columns: ['organization', 'repository'])]
class DockerImage extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $organization = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $repository = '';

    #[ORM\Column(type: 'text')]
    private string $description = '';

    #[ORM\OneToMany(mappedBy: 'dockerImage', targetEntity: DockerImageTag::class, orphanRemoval: true)]
    private Collection $dockerImageTags;

    public function __construct()
    {
        $this->dockerImageTags = new ArrayCollection();
    }

    public function __toString(): string
    {
        $name = $this->organization;

        if (!empty($this->repository)) {
            $name .= '/';
            $name .= $this->repository;
        }

        return empty($name) ? $this->getId()->jsonSerialize() : $name;
    }

    public function getOrganization(): ?string
    {
        return $this->organization;
    }

    public function setOrganization(string $organization): self
    {
        $this->organization = $organization;

        return $this;
    }

    public function getRepository(): ?string
    {
        return $this->repository;
    }

    public function setRepository(string $repository): self
    {
        $this->repository = $repository;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return Collection<int, DockerImageTag>
     */
    public function getDockerImageTags(): Collection
    {
        return $this->dockerImageTags;
    }

    public function addDockerImageTag(DockerImageTag $dockerImageTag): self
    {
        if (!$this->dockerImageTags->contains($dockerImageTag)) {
            $this->dockerImageTags[] = $dockerImageTag;
            $dockerImageTag->setDockerImage($this);
        }

        return $this;
    }

    public function removeDockerImageTag(DockerImageTag $dockerImageTag): self
    {
        if ($this->dockerImageTags->removeElement($dockerImageTag)) {
            // set the owning side to null (unless already changed)
            if ($dockerImageTag->getDockerImage() === $this) {
                $dockerImageTag->setDockerImage(null);
            }
        }

        return $this;
    }
}
