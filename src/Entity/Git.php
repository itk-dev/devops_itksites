<?php

namespace App\Entity;

use App\Repository\GitRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootDir', columns: ['server_id', 'root_dir'])]
class Git extends AbstractHandlerResult
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $tag = '';

    #[ORM\Column(type: 'text')]
    private $changes = '';

    #[ORM\Column(type: 'integer')]
    private int $changesCount = 0;

    #[ORM\ManyToMany(targetEntity: GitRemote::class, inversedBy: 'gits')]
    private $remotes;

    public function __construct()
    {
        $this->remotes = new ArrayCollection();
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

    public function getChanges(): ?string
    {
        return $this->changes;
    }

    public function setChanges(string $changes): self
    {
        $this->changes = $changes;

        return $this;
    }

    public function getChangesCount(): ?int
    {
        return $this->changesCount;
    }

    public function setChangesCount(int $changesCount): self
    {
        $this->changesCount = $changesCount;

        return $this;
    }

    /**
     * @return Collection<int, GitRemote>
     */
    public function getRemotes(): Collection
    {
        return $this->remotes;
    }

    public function addRemote(GitRemote $remote): self
    {
        if (!$this->remotes->contains($remote)) {
            $this->remotes[] = $remote;
        }

        return $this;
    }

    public function removeRemote(GitRemote $remote): self
    {
        $this->remotes->removeElement($remote);

        return $this;
    }

    public function clearRemotes(): self
    {
        $this->remotes->clear();

        return $this;
    }
}
