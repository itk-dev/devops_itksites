<?php

namespace App\Entity;

use App\Repository\SecurityContractRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SecurityContractRepository::class)]
class SecurityContract extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(unique: true)]
    private ?int $economicsId = null;

    #[ORM\Column(length: 255)]
    private string $projectName = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $hostingProvider = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentUrl = null;

    #[ORM\Column(nullable: true)]
    private ?float $monthlyPrice = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validFrom = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $validTo = null;

    #[ORM\Column]
    private bool $active = false;

    #[ORM\Column]
    private bool $eol = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $leantimeUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientContactName = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $clientContactEmail = null;

    #[ORM\Column]
    private bool $dedicatedServer = false;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $serverSize = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $gitRepos = null;

    #[ORM\Column(type: Types::JSON, nullable: true)]
    private ?array $systemOwnerNotices = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $projectTrackerKey = null;

    #[ORM\Column(nullable: true)]
    private ?float $quarterlyHours = null;

    #[ORM\Column(nullable: true)]
    private ?float $cybersecurityPrice = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $cybersecurityNote = null;

    public function __toString(): string
    {
        return $this->projectName;
    }

    public function getEconomicsId(): ?int
    {
        return $this->economicsId;
    }

    public function setEconomicsId(int $economicsId): static
    {
        $this->economicsId = $economicsId;

        return $this;
    }

    public function getProjectName(): string
    {
        return $this->projectName;
    }

    public function setProjectName(string $projectName): static
    {
        $this->projectName = $projectName;

        return $this;
    }

    public function getClientName(): ?string
    {
        return $this->clientName;
    }

    public function setClientName(?string $clientName): static
    {
        $this->clientName = $clientName;

        return $this;
    }

    public function getHostingProvider(): ?string
    {
        return $this->hostingProvider;
    }

    public function setHostingProvider(?string $hostingProvider): static
    {
        $this->hostingProvider = $hostingProvider;

        return $this;
    }

    public function getDocumentUrl(): ?string
    {
        return $this->documentUrl;
    }

    public function setDocumentUrl(?string $documentUrl): static
    {
        $this->documentUrl = $documentUrl;

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

    public function getValidFrom(): ?\DateTimeImmutable
    {
        return $this->validFrom;
    }

    public function setValidFrom(?\DateTimeImmutable $validFrom): static
    {
        $this->validFrom = $validFrom;

        return $this;
    }

    public function getValidTo(): ?\DateTimeImmutable
    {
        return $this->validTo;
    }

    public function setValidTo(?\DateTimeImmutable $validTo): static
    {
        $this->validTo = $validTo;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function isEol(): bool
    {
        return $this->eol;
    }

    public function setEol(bool $eol): static
    {
        $this->eol = $eol;

        return $this;
    }

    public function getLeantimeUrl(): ?string
    {
        return $this->leantimeUrl;
    }

    public function setLeantimeUrl(?string $leantimeUrl): static
    {
        $this->leantimeUrl = $leantimeUrl;

        return $this;
    }

    public function getClientContactName(): ?string
    {
        return $this->clientContactName;
    }

    public function setClientContactName(?string $clientContactName): static
    {
        $this->clientContactName = $clientContactName;

        return $this;
    }

    public function getClientContactEmail(): ?string
    {
        return $this->clientContactEmail;
    }

    public function setClientContactEmail(?string $clientContactEmail): static
    {
        $this->clientContactEmail = $clientContactEmail;

        return $this;
    }

    public function isDedicatedServer(): bool
    {
        return $this->dedicatedServer;
    }

    public function setDedicatedServer(bool $dedicatedServer): static
    {
        $this->dedicatedServer = $dedicatedServer;

        return $this;
    }

    public function getServerSize(): ?string
    {
        return $this->serverSize;
    }

    public function setServerSize(?string $serverSize): static
    {
        $this->serverSize = $serverSize;

        return $this;
    }

    public function getGitRepos(): ?string
    {
        return $this->gitRepos;
    }

    public function setGitRepos(?string $gitRepos): static
    {
        $this->gitRepos = $gitRepos;

        return $this;
    }

    public function getSystemOwnerNotices(): ?array
    {
        return $this->systemOwnerNotices;
    }

    public function setSystemOwnerNotices(?array $systemOwnerNotices): static
    {
        $this->systemOwnerNotices = $systemOwnerNotices;

        return $this;
    }

    public function getProjectTrackerKey(): ?string
    {
        return $this->projectTrackerKey;
    }

    public function setProjectTrackerKey(?string $projectTrackerKey): static
    {
        $this->projectTrackerKey = $projectTrackerKey;

        return $this;
    }

    public function getQuarterlyHours(): ?float
    {
        return $this->quarterlyHours;
    }

    public function setQuarterlyHours(?float $quarterlyHours): static
    {
        $this->quarterlyHours = $quarterlyHours;

        return $this;
    }

    public function getCybersecurityPrice(): ?float
    {
        return $this->cybersecurityPrice;
    }

    public function setCybersecurityPrice(?float $cybersecurityPrice): static
    {
        $this->cybersecurityPrice = $cybersecurityPrice;

        return $this;
    }

    public function getCybersecurityNote(): ?string
    {
        return $this->cybersecurityNote;
    }

    public function setCybersecurityNote(?string $cybersecurityNote): static
    {
        $this->cybersecurityNote = $cybersecurityNote;

        return $this;
    }
}
