<?php

namespace App\Entity;

use App\Repository\SecurityContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecurityContractRepository::class)]
class SecurityContract extends AbstractBaseEntity implements \Stringable
{
    #[ORM\OneToOne(inversedBy: 'securityContract')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Project $project = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $economicsReportUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $operationalContractUrl = null;

    #[ORM\Column(nullable: true)]
    private ?float $monthlyPrice = null;

    #[ORM\Column(nullable: true)]
    private ?float $quarterlyHours = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private ?\DateTimeImmutable $validTo = null;

    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\Column(type: Types::TEXT)]
    private string $notes = '';

    public function __toString(): string
    {
        return $this->project->getName();
    }

    public function getProject(): ?Project
    {
        return $this->project;
    }

    public function setProject(Project $project): static
    {
        $this->project = $project;

        return $this;
    }

    public function getEconomicsReportUrl(): ?string
    {
        return $this->economicsReportUrl;
    }

    public function setEconomicsReportUrl(?string $economicsReportUrl): static
    {
        $this->economicsReportUrl = $economicsReportUrl;

        return $this;
    }

    public function getOperationalContractUrl(): ?string
    {
        return $this->operationalContractUrl;
    }

    public function setOperationalContractUrl(?string $operationalContractUrl): static
    {
        $this->operationalContractUrl = $operationalContractUrl;

        return $this;
    }

    public function getMonthlyPrice(): ?float
    {
        return $this->monthlyPrice;
    }

    public function setMonthlyPrice(?float $monthlyPrice): static
    {
        $this->monthlyPrice = $monthlyPrice;

        return $this;
    }

    public function getQuarterlyHours(): ?float
    {
        return $this->quarterlyHours;
    }

    public function setQuarterlyHours(float $quarterlyHours): static
    {
        $this->quarterlyHours = $quarterlyHours;

        return $this;
    }

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(\DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->validTo;
    }

    public function setValidTo(\DateTimeImmutable $validTo): static
    {
        $this->validTo = $validTo;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(string $notes): static
    {
        $this->notes = $notes;

        return $this;
    }
}
