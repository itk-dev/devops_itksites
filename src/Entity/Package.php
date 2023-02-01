<?php

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\UniqueConstraint(name: 'vendor_package', columns: ['vendor', 'package'])]
class Package extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $vendor;

    #[ORM\Column(type: 'string', length: 255)]
    private string $package;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $homepage;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private ?string $license;

    #[ORM\Column(nullable: true)]
    private ?bool $abandoned = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $warning = null;

    #[ORM\OneToMany(mappedBy: 'package', targetEntity: PackageVersion::class)]
    private Collection $packageVersions;

    public function __construct()
    {
        $this->packageVersions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->vendor.'/'.$this->package;
    }

    public function getVendor(): ?string
    {
        return $this->vendor;
    }

    public function setVendor(string $vendor): self
    {
        $this->vendor = $vendor;

        return $this;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getHomepage(): ?string
    {
        return $this->homepage;
    }

    public function setHomepage(?string $homepage): self
    {
        $this->homepage = $homepage;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getLicense(): ?string
    {
        return $this->license;
    }

    public function setLicense(?string $license): self
    {
        $this->license = $license;

        return $this;
    }

    public function isAbandoned(): ?bool
    {
        return $this->abandoned;
    }

    public function setAbandoned(bool $abandoned): self
    {
        $this->abandoned = $abandoned;

        return $this;
    }

    public function getWarning(): ?string
    {
        return $this->warning;
    }

    public function setWarning(?string $warning): self
    {
        $this->warning = $warning;

        return $this;
    }

    /**
     * @return Collection<int, PackageVersion>
     */
    public function getPackageVersions(): Collection
    {
        return $this->packageVersions;
    }

    public function addPackageVersion(PackageVersion $packageVersion): self
    {
        if (!$this->packageVersions->contains($packageVersion)) {
            $this->packageVersions[] = $packageVersion;
            $packageVersion->setPackage($this);
        }

        return $this;
    }

    public function removePackageVersion(PackageVersion $packageVersion): self
    {
        if ($this->packageVersions->removeElement($packageVersion)) {
            // set the owning side to null (unless already changed)
            if ($packageVersion->getPackage() === $this) {
                $packageVersion->setPackage(null);
            }
        }

        return $this;
    }

    public function getPackagistUrl(): string
    {
        return 'https://packagist.org/packages/'.$this->vendor.'/'.$this->package;
    }
}
