<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ModuleRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleRepository::class)]
class Module extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $package;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $displayName = null;

    #[ORM\Column(type: 'boolean')]
    private bool $enabled;

    #[ORM\OneToMany(mappedBy: 'module', targetEntity: ModuleVersion::class)]
    private Collection $moduleVersions;

    public function __construct()
    {
        $this->moduleVersions = new ArrayCollection();
    }

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
}
