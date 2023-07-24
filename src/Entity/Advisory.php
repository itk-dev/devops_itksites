<?php

namespace App\Entity;

use App\Repository\AdvisoryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AdvisoryRepository::class)]
class Advisory extends AbstractBaseEntity
{
    #[ORM\Column(length: 255)]
    private ?string $advisoryId = null;

    #[ORM\Column(length: 255)]
    private ?string $affectedVersions = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    private ?string $cve = null;

    #[ORM\Column(length: 255)]
    private ?string $link = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $reportedAt = null;

    #[ORM\Column]
    private array $sources = [];

    #[ORM\ManyToOne(inversedBy: 'advisories')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Package $package = null;

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

    public function setSources(array $sources): self
    {
        $this->sources = $sources;

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
