<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DetectionResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DetectionResultRepository::class)]
#[ApiResource(collectionOperations: [
    "post" => ["messenger" => true, "output" => false, "status" => 202]
], itemOperations: [], denormalizationContext: ['groups' => ['write']]
)]
class DetectionResult extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["write"])]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(["write"])]
    private string $rootDir;

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'detectionResults')]
    #[ORM\JoinColumn(nullable: false)]
    private Server $server;

    #[ORM\Column(type: 'json')]
    #[Groups(["write"])]
    private array $data = [];

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRootDir(): ?string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function setServer(Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getData(): ?array
    {
        return $this->data;
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }
}
