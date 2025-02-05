<?php

declare(strict_types=1);

namespace App\Entity;

use App\Entity\ServiceCertificate\Service;
use App\Repository\ServiceCertificateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceCertificateRepository::class)]
class ServiceCertificate extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $domain = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $onePasswordUrl = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotNull]
    private ?\DateTimeInterface $expirationTime = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Url]
    private ?string $usageDocumentationUrl = null;

    #[ORM\OneToMany(targetEntity: Service::class, mappedBy: 'certificate', cascade: ['persist'], orphanRemoval: true)]
    #[ORM\OrderBy(['type' => 'ASC'])]
    #[Assert\Valid]
    private Collection $services;

    public function __construct()
    {
        $this->services = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return (string) $this->name;
    }

    public function getDomain(): ?string
    {
        return $this->domain;
    }

    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

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

    public function setDescription(string $description): self
    {
        $this->description = $description;

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

    public function getExpirationTime(): ?\DateTimeInterface
    {
        return $this->expirationTime;
    }

    public function setExpirationTime(\DateTimeInterface $expirationTime): self
    {
        $this->expirationTime = $expirationTime;

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

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->services;
    }

    public function addService(Service $service): self
    {
        if (!$this->services->contains($service)) {
            $this->services->add($service);
            $service->setCertificate($this);
        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        if ($this->services->removeElement($service)) {
            // set the owning side to null (unless already changed)
            if ($service->getCertificate() === $this) {
                $service->setCertificate(null);
            }
        }

        return $this;
    }
}
