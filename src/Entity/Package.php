<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\PackageRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PackageRepository::class)]
#[ORM\UniqueConstraint(name: 'vendor_name', columns: ['vendor', 'name'])]
class Package extends AbstractBaseEntity implements \Stringable
{
    private const string PACKAGIST_URL_PATTERN = 'https://packagist.org/packages/%s/%s';

    #[ORM\Column(type: 'string', length: 255)]
    private string $vendor;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $homepage = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $type = null;

    #[ORM\Column(type: 'string', length: 25, nullable: true)]
    private ?string $license = null;

    #[ORM\Column(nullable: true)]
    private ?bool $abandoned = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $warning = null;

    #[ORM\OneToMany(mappedBy: 'package', targetEntity: PackageVersion::class)]
    private Collection $packageVersions;

    #[ORM\OneToMany(mappedBy: 'package', targetEntity: Advisory::class)]
    private Collection $advisories;

    #[ORM\Column]
    private int $advisoryCount = 0;

    public function __construct()
    {
        $this->packageVersions = new ArrayCollection();
        $this->advisories = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->vendor.'/'.$this->name;
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

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

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
        return \sprintf(self::PACKAGIST_URL_PATTERN, $this->vendor, $this->name);
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
            $advisory->setPackage($this);
        }

        $this->advisoryCount = $this->advisories->count();

        return $this;
    }

    public function removeAdvisory(Advisory $advisory): self
    {
        if ($this->advisories->removeElement($advisory)) {
            // set the owning side to null (unless already changed)
            if ($advisory->getPackage() === $this) {
                $advisory->setPackage(null);
            }
        }

        $this->advisoryCount = $this->advisories->count();

        return $this;
    }

    public function getAdvisoryCount(): int
    {
        return $this->advisoryCount;
    }

    private function setAdvisoryCount(int $advisoryCount): self
    {
        $this->advisoryCount = $advisoryCount;

        return $this;
    }
}
