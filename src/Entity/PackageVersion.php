<?php

namespace App\Entity;

use App\Repository\PackageVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageVersionRepository::class)]
class PackageVersion extends AbstractBaseEntity
{
    #[ORM\ManyToMany(targetEntity: Installation::class, inversedBy: 'packageVersions')]
    private Collection $installations;

    #[ORM\ManyToOne(targetEntity: Package::class, inversedBy: 'packageVersions')]
    #[ORM\JoinColumn(nullable: false)]
    private Package $package;

    #[ORM\Column(type: 'string', length: 25)]
    private string $version;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private ?string $latest;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $latestStatus;

    public function __construct()
    {
        $this->installations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->package.':'.$this->version;
    }

    /**
     * @return Collection<id, Installation>
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

    public function getPackage(): ?Package
    {
        return $this->package;
    }

    public function setPackage(?Package $package): self
    {
        $this->package = $package;

        return $this;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function setVersion(string $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getLatest(): ?string
    {
        return $this->latest;
    }

    public function setLatest(?string $latest): self
    {
        $this->latest = $latest;

        return $this;
    }

    public function getLatestStatus(): ?string
    {
        return $this->latestStatus;
    }

    public function setLatestStatus(?string $latestStatus): self
    {
        $this->latestStatus = $latestStatus;

        return $this;
    }

    public function getPackagistUrl(): string
    {
        return $this->package->getPackagistUrl();
    }
}
