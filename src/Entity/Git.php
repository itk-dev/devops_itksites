<?php

namespace App\Entity;

use App\Repository\GitRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: GitRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootDir', columns: ['server_id', 'root_dir'])]
class Git extends AbstractHandlerResult
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $remote = '';

    #[ORM\Column(type: 'string', length: 255)]
    private string $tag = '';

    #[ORM\Column(type: 'text')]
    private $changes = '';

    public function getRemote(): ?string
    {
        return $this->remote;
    }

    public function setRemote(string $remote): self
    {
        $this->remote = $remote;

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

    public function getChanges(): ?string
    {
        return $this->changes;
    }

    public function setChanges(string $changes): self
    {
        $this->changes = $changes;

        return $this;
    }
}
