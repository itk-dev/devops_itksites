<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageVersionRepository::class)]
#[ORM\UniqueConstraint(name: 'package_version', columns: ['package_id', 'version'])]
class PackageVersion extends AbstractBaseEntity implements \Stringable
{
    #[ORM\ManyToMany(targetEntity: Installation::class, mappedBy: 'packageVersions')]
    private Collection $installations;

    #[ORM\ManyToOne(targetEntity: Package::class, inversedBy: 'packageVersions')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Package $package;

    #[ORM\Column(type: 'string', length: 255)]
    private string $version;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $latest = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $latestStatus = null;

    #[ORM\ManyToMany(targetEntity: Advisory::class, inversedBy: 'packageVersions')]
    private Collection $advisories;

    #[ORM\Column]
    private int $advisoryCount = 0;

    public function __construct()
    {
        $this->installations = new ArrayCollection();
        $this->advisories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->package.':'.$this->version;
    }

    /**
     * @return Collection<Installation>
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

    /**
     * @return Collection<int, Advisory>
     */
    public function getAdvisories(): Collection
    {
        return $this->advisories;
    }

    public function addAdvisory(Advisory $advisory): self
    {
        if (!$this->advisories->contains($advisory)) {
            $this->advisories->add($advisory);
        }

        $this->advisoryCount = $this->advisories->count();

        return $this;
    }

    public function removeAdvisory(Advisory $advisory): self
    {
        $this->advisories->removeElement($advisory);

        $this->advisoryCount = $this->advisories->count();

        return $this;
    }

    public function getAdvisoryCount(): int
    {
        return $this->advisoryCount;
    }
}
