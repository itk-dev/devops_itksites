<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\DomainRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DomainRepository::class)]
#[ORM\UniqueConstraint(name: 'site_address_idx', fields: ['site', 'address'])]
class Domain extends AbstractHandlerResult implements \Stringable
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: 'Your address must be longer than {{ limit }} characters',
        maxMessage: 'Your address cannot be longer than {{ limit }} characters',
    )]
    private string $address;

    #[ORM\ManyToOne(targetEntity: Site::class, inversedBy: 'domains')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Site $site;

    public function __toString(): string
    {
        return $this->address;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): self
    {
        $this->address = $address;

        return $this;
    }

    public function getSite(): Site
    {
        return $this->site;
    }

    public function setSite(Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}
