<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use App\Repository\DetectionResultRepository;
use App\Utils\RootDirNormalizer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Post(status: 202, output: false, messenger: true),
    ],
    denormalizationContext: ['groups' => ['write']],
)]
#[ORM\Entity(repositoryClass: DetectionResultRepository::class)]
#[ORM\UniqueConstraint(name: 'server_hash_idx', fields: ['server', 'hash'])]
#[ORM\Index(name: 'type_idx', columns: ['type'])]
class DetectionResult extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    private string $type = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    private string $rootDir = '';

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'detectionResults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Server $server;

    #[ORM\Column(type: 'text')]
    #[Groups(['write'])]
    private string $data = '';

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $hash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastContact;

    #[\Override]
    public function __toString(): string
    {
        return '['.$this->type.'] '.$this->server.$this->rootDir.' @ '.$this->lastContact->format(DATE_ATOM);
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getRootDir(): string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = RootDirNormalizer::normalize($rootDir);

        return $this;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function getPrettyData(): string
    {
        try {
            $json = json_decode($this->data, false, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
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
        $this->hash = sha1($this->server->getId().$this->type.$this->rootDir.$this->data);

        return $this;
    }

    public function getLastContact(): ?\DateTimeImmutable
    {
        return $this->lastContact;
    }

    public function setLastContact(?\DateTimeImmutable $lastContact = null): self
    {
        $this->lastContact = $lastContact ?? new \DateTimeImmutable();

        return $this;
    }
}
