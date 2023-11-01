<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\OIDCRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OIDCRepository::class)]
class OIDC extends AbstractBaseEntity
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Groups(['export'])]
    #[SerializedName('Domian')]
    private ?string $domain = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Groups(['export'])]
    #[SerializedName('Expiration time')]
    private ?\DateTimeInterface $expirationTime = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['export'])]
    #[SerializedName('1Password URL')]
    private ?string $onePasswordUrl = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    #[Groups(['export'])]
    #[SerializedName('Usage documentation URL')]
    private ?string $usageDocumentationUrl = null;

    #[ORM\Column(length: 10)]
    #[Groups(['export'])]
    #[SerializedName('Type')]
    private ?string $type = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $notes = null;

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getExpirationTime(): ?\DateTimeInterface
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(\DateTimeInterface $expirationTime): self
    {
        $this->expirationTime = $expirationTime;

        return $this;
    }

    public function getOnePasswordUrl(): ?string
    {
        return $this->onePasswordUrl;
    }

    public function setOnePasswordUrl(string $onePasswordUrl): self
    {
        $this->onePasswordUrl = $onePasswordUrl;

        return $this;
    }

    public function getUsageDocumentationUrl(): ?string
    {
        return $this->usageDocumentationUrl;
    }

    public function setUsageDocumentationUrl(string $usageDocumentationUrl): self
    {
        $this->usageDocumentationUrl = $usageDocumentationUrl;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getNotes(): ?string
    {
        return $this->notes;
    }

    public function setNotes(?string $notes): self
    {
        $this->notes = $notes;

        return $this;
    }
}
