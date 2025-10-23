<?php

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(unique: true)]
    private ?int $leantimeId;

    #[ORM\Column(length: 255)]
    private string $name;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $LeantimeUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $EconomicsUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $details;

    #[ORM\Column]
    private ?\DateTimeImmutable $leantimeModifiedAt = null;

    #[ORM\OneToOne(mappedBy: 'project', cascade: ['persist', 'remove'])]
    private ?SecurityContract $securityContract = null;

    public function __construct(int $leantimeId, string $name, ?string $details = null)
    {
        $this->leantimeId = $leantimeId;
        $this->name = $name;
        $this->details = $details;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLeantimeUrl(): ?string
    {
        return $this->LeantimeUrl;
    }

    public function setLeantimeUrl(string $LeantimeUrl): static
    {
        $this->LeantimeUrl = $LeantimeUrl;

        return $this;
    }

    public function getEconomicsUrl(): ?string
    {
        return $this->EconomicsUrl;
    }

    public function setEconomicsUrl(string $EconomicsUrl): static
    {
        $this->EconomicsUrl = $EconomicsUrl;

        return $this;
    }

    public function getDetails(): ?string
    {
        return $this->details;
    }

    public function setDetails(?string $details): static
    {
        $this->details = $details;

        return $this;
    }

    public function getLeantimeId(): ?int
    {
        return $this->leantimeId;
    }

    public function setLeantimeId(int $leantimeId): static
    {
        $this->leantimeId = $leantimeId;

        return $this;
    }

    public function getLeantimeModifiedAt(): ?\DateTimeImmutable
    {
        return $this->leantimeModifiedAt;
    }

    public function setLeantimeModifiedAt(\DateTimeImmutable $leantimeModifiedAt): static
    {
        $this->leantimeModifiedAt = $leantimeModifiedAt;

        return $this;
    }

    public function getDetailsText(): ?string
    {
        return $this->detailsText;
    }

    public function setDetailsText(string $detailsText): static
    {
        $this->detailsText = $detailsText;

        return $this;
    }

    public function getSecurityContract(): ?SecurityContract
    {
        return $this->securityContract;
    }

    public function setSecurityContract(SecurityContract $securityContract): static
    {
        // set the owning side of the relation if necessary
        if ($securityContract->getProject() !== $this) {
            $securityContract->setProject($this);
        }

        $this->securityContract = $securityContract;

        return $this;
    }
}
