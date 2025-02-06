<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DockerImageTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DockerImageTagRepository::class)]
#[ORM\UniqueConstraint(name: 'dockerImage_name_tag', columns: ['docker_image_id', 'name', 'tag'])]
class DockerImageTag extends AbstractBaseEntity implements \Stringable
{
    #[ORM\ManyToMany(targetEntity: Installation::class, mappedBy: 'dockerImageTags')]
    private Collection $installations;

    #[ORM\Column(type: 'string', length: 50)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 50)]
    private string $tag = '';

    #[ORM\ManyToOne(targetEntity: DockerImage::class, inversedBy: 'dockerImageTags')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DockerImage $dockerImage;

    public function __construct()
    {
        $this->installations = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->dockerImage.':'.$this->getTag();
    }

    /**
     * @return Collection<int, Installation>
     */
    public function getInstallations(): Collection
    {
        return $this->installations;
    }

    public function addInstallation(Installation $installation): self
    {
        if (!$this->installations->contains($installation)) {
            $this->installations[] = $installation;
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): self
    {
        $this->installations->removeElement($installation);

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getTag(): ?string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getDockerImage(): ?DockerImage
    {
        return $this->dockerImage;
    }

    public function setDockerImage(?DockerImage $dockerImage): self
    {
        $this->dockerImage = $dockerImage;

        return $this;
    }
}
