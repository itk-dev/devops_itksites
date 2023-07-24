<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Component\Uid\Ulid;

#[ORM\MappedSuperclass]
#[ORM\HasLifecycleCallbacks]
abstract class AbstractBaseEntity
{
    #[ApiProperty(identifier: true)]
    #[ORM\Id]
    #[ORM\Column(type: 'ulid', unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    protected Ulid $id;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected \DateTimeImmutable $createdAt;

    #[ORM\Column(type: 'datetime_immutable', nullable: false)]
    protected \DateTimeImmutable $modifiedAt;

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    protected string $createdBy = '';

    #[ORM\Column(type: 'string', nullable: false, options: ['default' => ''])]
    protected string $modifiedBy = '';

    /**
     * Get the Ulid.
     */
    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * Set the Ulid.
     */
    public function setId(Ulid $id): self
    {
        $this->id = $id;

        $this->createdAt = $this->id->getDateTime();

        return $this;
    }

    public function getCreatedAt(): \DateTimeInterface
    {
        return $this->createdAt;
    }

    #[ORM\PrePersist]
    public function setCreatedAtValue(): self
    {
        $this->createdAt = isset($this->id) ? $this->id->getDateTime() : new \DateTimeImmutable();

        return $this;
    }

    public function getModifiedAt(): \DateTimeInterface
    {
        return $this->modifiedAt;
    }

    #[ORM\PrePersist]
    #[ORM\PreUpdate]
    public function setModifiedAtValue(): self
    {
        $this->modifiedAt = new \DateTimeImmutable();

        return $this;
    }

    public function getModifiedBy(): string
    {
        return $this->modifiedBy;
    }

    public function getCreatedBy(): string
    {
        return $this->createdBy;
    }
}
