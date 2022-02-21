<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DetectionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DetectionRepository::class)]
#[ApiResource]
class Detection extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private $Type;

    #[ORM\Column(type: 'json')]
    private array $Data = [];

    public function getType(): ?string
    {
        return $this->Type;
    }

    public function setType(string $Type): self
    {
        $this->Type = $Type;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->Data;
    }

    public function setData(array $Data): self
    {
        $this->Data = $Data;

        return $this;
    }
}
