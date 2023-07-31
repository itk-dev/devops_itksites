<?php

namespace App\Entity;

use App\Repository\OIDCRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OIDCRepository::class)]
class OIDC extends AbstractBaseEntity
{
    #[ORM\Column(length: 255)]
    private ?string $site = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $expirationDate = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $claims = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $ad = null;

    #[ORM\Column(length: 255)]
    private ?string $discoveryUrl = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $graph = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $onePasswordUrl = null;

    public function getSite(): ?string
    {
        return $this->site;
    }

    public function setSite(string $site): self
    {
        $this->site = $site;

        return $this;
    }

    public function getExpirationDate(): ?\DateTimeInterface
    {
        return $this->expirationDate;
    }

    public function setExpirationDate(\DateTimeInterface $expirationDate): self
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    public function getClaims(): ?string
    {
        return $this->claims;
    }

    public function setClaims(string $claims): self
    {
        $this->claims = $claims;

        return $this;
    }

    public function getAd(): ?string
    {
        return $this->ad;
    }

    public function setAd(string $ad): self
    {
        $this->ad = $ad;

        return $this;
    }

    public function getDiscoveryUrl(): ?string
    {
        return $this->discoveryUrl;
    }

    public function setDiscoveryUrl(string $discoveryUrl): self
    {
        $this->discoveryUrl = $discoveryUrl;

        return $this;
    }

    public function getGraph(): ?string
    {
        return $this->graph;
    }

    public function setGraph(?string $graph): self
    {
        $this->graph = $graph;

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
}
