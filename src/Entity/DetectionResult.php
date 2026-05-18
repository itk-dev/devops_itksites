<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Repository\DetectionResultRepository;
use App\Types\DetectionType;
use App\Utils\RootDirNormalizer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ApiResource(
    operations: [
        new Post(
            status: 202,
            output: false,
            messenger: true,
            openapi: new Model\Operation(
                summary: 'Submit a detection result for async processing',
                description: 'Accepts a detection result from the server harvester and queues it for asynchronous processing. The result is deduplicated by content hash — identical submissions update the last contact timestamp without triggering reprocessing. Returns 202 Accepted with an empty body.',
                responses: [
                    '202' => new Model\Response(
                        description: 'Detection result accepted for processing',
                    ),
                    '400' => new Model\Response(
                        description: 'Invalid input — malformed request body',
                    ),
                    '401' => new Model\Response(
                        description: 'Unauthorized — missing or invalid API key. The Authorization header must use the format: Apikey {key}',
                    ),
                    '403' => new Model\Response(
                        description: 'Forbidden — the authenticated server does not have the required ROLE_SERVER role',
                    ),
                    '422' => new Model\Response(
                        description: 'Validation error — one or more fields failed constraint validation',
                    ),
                ],
            ),
        ),
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
    #[ApiProperty(
        description: 'The type of detection result, determines which handler processes the data',
        example: DetectionType::NGINX,
        schema: ['enum' => [DetectionType::DIRECTORY, DetectionType::DOCKER, DetectionType::DRUPAL, DetectionType::GIT, DetectionType::NGINX, DetectionType::SYMFONY]],
    )]
    private string $type = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[ApiProperty(
        description: 'Absolute path to the root directory of the detected installation on the server',
        example: '/data/www/example-site/htdocs',
    )]
    private string $rootDir = '';

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'detectionResults')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Server $server;

    #[ORM\Column(type: 'text')]
    #[Groups(['write'])]
    #[ApiProperty(
        description: 'JSON-encoded payload from the server harvester containing the detection details. Structure varies by type.',
        example: '{"packages":{"symfony/framework-bundle":{"version":"7.2.1"}}}',
    )]
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
