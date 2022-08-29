<?php

namespace App\Entity;

use App\Repository\GitRemoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitRemoteRepository::class)]
class GitRemote extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $url;

    #[ORM\ManyToMany(targetEntity: Git::class, mappedBy: 'remotes', orphanRemoval: true, cascade: ['persist'])]
    private $gits;

    public function __construct($url)
    {
        $this->gits = new ArrayCollection();
        $this->setUrl($url);
    }

    public function __toString(): string
    {
        return $this->getUrl();
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(string $url): self
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return Collection<int, Git>
     */
    public function getGits(): Collection
    {
        return $this->gits;
    }

    public function addGit(Git $git): self
    {
        if (!$this->gits->contains($git)) {
            $this->gits[] = $git;
            $git->addRemote($this);
        }

        return $this;
    }

    public function removeGit(Git $git): self
    {
        if ($this->gits->removeElement($git)) {
            $git->removeRemote($this);
        }

        return $this;
    }
}
