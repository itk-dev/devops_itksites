<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ModuleVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ModuleVersionRepository::class)]
#[ORM\UniqueConstraint(name: 'module_version', columns: ['module_id', 'version'])]
class ModuleVersion extends AbstractBaseEntity implements \Stringable
{
    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'moduleVersions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Module $module;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $version = null;

    #[ORM\ManyToMany(targetEntity: Installation::class, mappedBy: 'moduleVersions')]
    private Collection $installations;

    #[ORM\ManyToOne(targetEntity: PackageVersion::class, inversedBy: 'moduleVersions')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PackageVersion $composerPackageVersion = null;

    #[\Override]
    public function __toString(): string
    {
        return $this->getVersion();
    }

    /**
     * @throws \Exception
     */
    public function display(int $style): string
    {
        return match ($style) {
            0 => $this->getVersion(),
            1 => $this->getModule().':'.$this->getVersion(),
            default => throw new \Exception('Unknown style'),
        };
    }

    public function __construct()
    {
        $this->installations = new ArrayCollection();
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(?Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version ?? 'Unknown';
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

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
            $this->installations[] = $installation;
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): self
    {
        $this->installations->removeElement($installation);

        return $this;
    }

    public function getComposerPackageVersion(): ?PackageVersion
    {
        return $this->composerPackageVersion;
    }

    public function setComposerPackageVersion(?PackageVersion $composerPackageVersion): self
    {
        $this->composerPackageVersion = $composerPackageVersion;

        return $this;
    }

    /**
     * @return Collection<int, Advisory>
     */
    public function getAdvisories(): Collection
    {
        return $this->composerPackageVersion?->getAdvisories() ?? new ArrayCollection();
    }

    public function getAdvisoryCount(): int
    {
        return $this->composerPackageVersion?->getAdvisoryCount() ?? 0;
    }
}
