<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $package;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $name;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $enabled;

    #[ORM\OneToMany(targetEntity: ModuleVersion::class, mappedBy: 'module')]
    private Collection $moduleVersions;

    #[ORM\ManyToOne(targetEntity: Package::class, inversedBy: 'modules')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?Package $composerPackage = null;

    public function __construct()
    {
        $this->moduleVersions = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->displayName ?? $this->package.'/'.$this->name;
    }

    public function getPackage(): ?string
    {
        return $this->package;
    }

    public function setPackage(string $package): self
    {
        $this->package = $package;

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

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): self
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return Collection<int, ModuleVersion>
     */
    public function getModuleVersions(): Collection
    {
        return $this->moduleVersions;
    }

    public function addModuleVersion(ModuleVersion $moduleVersion): self
    {
        if (!$this->moduleVersions->contains($moduleVersion)) {
            $this->moduleVersions[] = $moduleVersion;
            $moduleVersion->setModule($this);
        }

        return $this;
    }

    public function removeModuleVersion(ModuleVersion $moduleVersion): self
    {
        $this->moduleVersions->removeElement($moduleVersion);

        return $this;
    }

    public function getComposerPackage(): ?Package
    {
        return $this->composerPackage;
    }

    public function setComposerPackage(?Package $composerPackage): self
    {
        $this->composerPackage = $composerPackage;

        return $this;
    }

    /**
     * @return Collection<int, Advisory>
     */
    public function getAdvisories(): Collection
    {
        return $this->composerPackage?->getAdvisories() ?? new ArrayCollection();
    }

    public function getAdvisoryCount(): int
    {
        return $this->composerPackage?->getAdvisoryCount() ?? 0;
    }
}
