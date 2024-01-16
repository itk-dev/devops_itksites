<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\GitTagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitTagRepository::class)]
class GitTag extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $tag = '';

    #[ORM\ManyToOne(targetEntity: GitRepo::class, inversedBy: 'gitTags')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private GitRepo $repo;

    #[ORM\OneToMany(mappedBy: 'gitTag', targetEntity: Installation::class)]
    private Collection $installations;

    public function __toString(): string
    {
        return $this->repo->__toString().'@'.$this->getTag();
    }

    public function __construct()
    {
        $this->installations = new ArrayCollection();
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

    public function getRepo(): GitRepo
    {
        return $this->repo;
    }

    public function setRepo(GitRepo $repo): self
    {
        $this->repo = $repo;

        return $this;
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
            $this->installations->add($installation);
            $installation->setGitTag($this);
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): self
    {
        if ($this->installations->removeElement($installation)) {
            // set the owning side to null (unless already changed)
            if ($installation->getGitTag() === $this) {
                $installation->setGitTag(null);
            }
        }

        return $this;
    }
}
