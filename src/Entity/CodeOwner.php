<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\CodeOwnerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CodeOwnerRepository::class)]
class CodeOwner extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(unique: true)]
    private ?int $economicsId = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255)]
    private string $email = '';

    /**
     * @var Collection<int, Project>
     */
    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'codeOwners')]
    private Collection $projects;

    public function __construct()
    {
        $this->projects = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->name;
    }

    public function getEconomicsId(): ?int
    {
        return $this->economicsId;
    }

    public function setEconomicsId(int $economicsId): static
    {
        $this->economicsId = $economicsId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }
}
