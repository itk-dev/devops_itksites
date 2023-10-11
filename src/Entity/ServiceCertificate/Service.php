<?php

declare(strict_types=1);

namespace App\Entity\ServiceCertificate;

use App\Entity\AbstractBaseEntity;
use App\Entity\ServiceCertificate;
use App\Repository\ServiceCertificate\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
#[ORM\Table(name: 'service_certificate_service')]
class Service extends AbstractBaseEntity
{
    #[ORM\ManyToOne(inversedBy: 'services')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ServiceCertificate $certificate = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $type = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $onePasswordUrl = null;

    public function __toString(): string
    {
        return $this->type;
    }

    public function getCertificate(): ?ServiceCertificate
    {
        return $this->certificate;
    }

    public function setCertificate(?ServiceCertificate $certificate): self
    {
        $this->certificate = $certificate;

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
