<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\DetectionResultRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: DetectionResultRepository::class)]
#[ApiResource(
//    collectionOperations: [
//    'post' => ['messenger' => true, 'output' => false, 'status' => 202],
//],
//    itemOperations: [],
//    denormalizationContext: ['groups' => ['write']]
)]
class DetectionResult extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    private string $type;

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    private string $rootDir;

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'detectionResults')]
    #[ORM\JoinColumn(nullable: false)]
    private Server $server;

    #[ORM\Column(type: 'text')]
    #[Groups(['write'])]
    private string $data = '';

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $hash;

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

    public function getData(): ?string
    {
        return $this->data;
    }

    public function getPrettyData(): ?string
    {
        try {
            $json = json_decode($this->data, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            return $this->data;
        }

        return json_encode($json, JSON_PRETTY_PRINT);
    }

    public function setData(string $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function generateHash(): self
    {
        $this->hash = sha1($this->type.$this->server->getId().$this->data);

        return $this;
    }
}
