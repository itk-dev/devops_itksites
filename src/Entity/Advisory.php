<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdvisoryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdvisoryRepository::class)]
class Advisory extends AbstractBaseEntity
{
    private const GITHUB_ADVISORY_URL_PATTERN = 'https://github.com/advisories/%s';
    private const FRIENDS_OF_PHP_ADVISORY_URL_PATTERN = 'https://github.com/FriendsOfPHP/security-advisories/blob/master/%s';

    #[ORM\Column(length: 255, unique: true)]
    private ?string $advisoryId = null;

    #[ORM\Column(length: 500)]
    private ?string $affectedVersions = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $cve = null;

    #[ORM\Column(length: 255)]
    private ?string $link = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $reportedAt = null;

    #[ORM\Column(type: "json")]
    private array $sources = [];

    #[ORM\ManyToMany(targetEntity: PackageVersion::class, mappedBy: 'advisories')]
    private Collection $packageVersions;

    #[ORM\ManyToOne(inversedBy: 'advisories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Package $package = null;

    public function __construct()
    {
        $this->packageVersions = new ArrayCollection();
    }

    public function __toString(): string
    {
        $id = $this->cve ?? $this->advisoryId;

        return $id.' | '.$this->title;
    }

    public function getAdvisoryId(): ?string
    {
        return $this->advisoryId;
    }

    public function setAdvisoryId(string $advisoryId): self
    {
        $this->advisoryId = $advisoryId;

        return $this;
    }

    public function getAffectedVersions(): ?string
    {
        return $this->affectedVersions;
    }

    public function setAffectedVersions(string $affectedVersions): self
    {
        $this->affectedVersions = $affectedVersions;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getCve(): ?string
    {
        return $this->cve;
    }

    public function setCve(string $cve): self
    {
        $this->cve = $cve;

        return $this;
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(string $link): self
    {
        $this->link = $link;

        return $this;
    }

    public function getReportedAt(): ?\DateTimeImmutable
    {
        return $this->reportedAt;
    }

    public function setReportedAt(\DateTimeImmutable $reportedAt): self
    {
        $this->reportedAt = $reportedAt;

        return $this;
    }

    public function getSources(): array
    {
        return $this->sources;
    }

    public function getSourceLinks(): array
    {
        $links = [];

        foreach ($this->getSources() as $source) {
            $links[] = match ($source['name']) {
                'GitHub' => sprintf(self::GITHUB_ADVISORY_URL_PATTERN, $source['remoteId']),
                'FriendsOfPHP/security-advisories' => sprintf(self::FRIENDS_OF_PHP_ADVISORY_URL_PATTERN, $source['remoteId']),
                default => $source['name'].' / '.$source['remoteId'],
            };
        }

        return $links;
    }

    public function setSources(array $sources): self
    {
        $this->sources = $sources;

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
        if (null === $this->package) {
            $packageVersion->getPackage()->addAdvisory($this);
        } else {
            if ($this->package !== $packageVersion->getPackage()) {
                throw new \InvalidArgumentException('All packageVersions for an advisory must belong to the same package');
            }
        }

        if (!$this->packageVersions->contains($packageVersion)) {
            $this->packageVersions->add($packageVersion);
            $packageVersion->addAdvisory($this);
        }

        return $this;
    }

    public function removePackageVersion(PackageVersion $packageVersion): self
    {
        if ($this->packageVersions->removeElement($packageVersion)) {
            $packageVersion->removeAdvisory($this);
        }

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
}
