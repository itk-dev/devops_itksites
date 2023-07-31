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

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $expirationTime = null;

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

    public function getExpirationTime(): ?\DateTimeInterface
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(\DateTimeInterface $expirationTime): self
    {
        $this->expirationTime = $expirationTime;

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
