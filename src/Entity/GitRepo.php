<?php

namespace App\Entity;

use App\Repository\GitRepoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitRepoRepository::class)]
#[ORM\UniqueConstraint(name: 'provider_org_repo', columns: ['provider', 'organization', 'repo'])]
class GitRepo extends AbstractBaseEntity
{
    #[ORM\Column(length: 255)]
    private string $provider = '';

    #[ORM\Column(length: 255)]
    private string $organization = '';

    #[ORM\Column(length: 255)]
    private string $repo = '';

    #[ORM\OneToMany(mappedBy: 'repo', targetEntity: GitTag::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $gitTags;

    public function __construct()
    {
        $this->gitTags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getOrganization().'/'.$this->getRepo();
    }

    public function getUrl(): string
    {
        return 'https://'.$this->getProvider().'/'.$this->getOrganization().'/'.$this->getRepository();
    }

    /**
     * @return Collection<int, GitTag>
     */
    public function getGitTags(): Collection
    {
        return $this->gitTags;
    }

    public function addGitTag(GitTag $gitTag): self
    {
        if (!$this->gitTags->contains($gitTag)) {
            $this->gitTags[] = $gitTag;
            $gitTag->setRepo($this);
        }

        return $this;
    }

    public function removeGitTag(GitTag $gitTag): self
    {
        if ($this->gitTags->removeElement($gitTag)) {
            $gitTag->setRepo(null);
        }

        return $this;
    }

    public function getProvider(): ?string
    {
        return $this->provider;
    }

    public function setProvider(string $provider): self
    {
        $this->provider = $provider;

        return $this;
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

    public function getRepo(): ?string
    {
        return $this->repo;
    }

    public function setRepo(string $repo): self
    {
        $this->repo = $repo;

        return $this;
    }
}
