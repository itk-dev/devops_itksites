<?php

declare(strict_types=1);

namespace App\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Post;
use ApiPlatform\OpenApi\Model;
use App\Repository\DeployResultRepository;
use App\Utils\RootDirNormalizer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A deployment reporting what it put on a server.
 *
 * Sites deployed from a prebuilt artifact have no .git directory on disk, so the
 * server harvester cannot detect their origin and tag. The deployment is the only
 * party that knows them, so it reports them here.
 *
 * This is a source record, not derived data: it is persisted so that the derived
 * tables can be rebuilt from it, the same way detection results are replayed.
 */
#[ApiResource(
    operations: [
        new Post(
            security: "is_granted('ROLE_DEPLOYER')",
            status: 202,
            output: false,
            messenger: true,
            openapi: new Model\Operation(
                summary: 'Report a deployment for async processing',
                description: 'Accepts a deployment report from a CI pipeline and queues it for asynchronous processing. The target server is identified by name, and the installation by its root directory. Identical submissions update the last contact timestamp without triggering reprocessing. Returns 202 Accepted with an empty body.',
                responses: [
                    '202' => new Model\Response(
                        description: 'Deploy result accepted for processing',
                    ),
                    '400' => new Model\Response(
                        description: 'Invalid input — malformed request body',
                    ),
                    '401' => new Model\Response(
                        description: 'Unauthorized — missing or invalid API key. The Authorization header must use the format: Apikey {key}',
                    ),
                    '403' => new Model\Response(
                        description: 'Forbidden — the API key does not have the required ROLE_DEPLOYER role',
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
#[ORM\Entity(repositoryClass: DeployResultRepository::class)]
#[ORM\UniqueConstraint(name: 'deploy_server_hash_idx', fields: ['server', 'hash'])]
class DeployResult extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[Assert\NotBlank]
    #[ApiProperty(
        description: 'Name of the server deployed to, as registered in itksites. This is the host the deployment connects to over SSH.',
        example: 'srvitkphp84.itkdev.dk',
    )]
    private string $serverName = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[Assert\NotBlank]
    #[ApiProperty(
        description: 'Absolute path to the root directory of the deployed installation on the server',
        example: '/data/www/example-site/htdocs',
    )]
    private string $rootDir = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[Assert\NotBlank]
    #[ApiProperty(
        description: 'Clone URL of the repository that was deployed',
        example: 'https://github.com/itk-dev/example-site.git',
    )]
    private string $repoUrl = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[Assert\NotBlank]
    #[ApiProperty(
        description: 'The tag that was deployed',
        example: '1.4.2',
    )]
    private string $tag = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[ApiProperty(
        description: 'Commit the deployed tag points at. Optional.',
        example: '9fceb02d0ae598e95dc970b74767f19372d61af8',
    )]
    private string $commit = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Groups(['write'])]
    #[ApiProperty(
        description: 'URL of the pipeline run that performed the deployment. Optional.',
        example: 'https://woodpecker.itkdev.dk/repos/42/pipeline/128',
    )]
    private string $pipelineUrl = '';

    #[ORM\ManyToOne(targetEntity: Server::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Server $server;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $hash;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $lastContact;

    #[\Override]
    public function __toString(): string
    {
        return $this->serverName.$this->rootDir.' @ '.$this->tag;
    }

    public function getServerName(): string
    {
        return $this->serverName;
    }

    public function setServerName(string $serverName): self
    {
        $this->serverName = $serverName;

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

    public function getRepoUrl(): string
    {
        return $this->repoUrl;
    }

    public function setRepoUrl(string $repoUrl): self
    {
        $this->repoUrl = $repoUrl;

        return $this;
    }

    public function getTag(): string
    {
        return $this->tag;
    }

    public function setTag(string $tag): self
    {
        $this->tag = $tag;

        return $this;
    }

    public function getCommit(): string
    {
        return $this->commit;
    }

    public function setCommit(string $commit): self
    {
        $this->commit = $commit;

        return $this;
    }

    public function getPipelineUrl(): string
    {
        return $this->pipelineUrl;
    }

    public function setPipelineUrl(string $pipelineUrl): self
    {
        $this->pipelineUrl = $pipelineUrl;

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

    public function getHash(): ?string
    {
        return $this->hash;
    }

    public function generateHash(): self
    {
        $this->hash = sha1($this->server->getId().$this->rootDir.$this->repoUrl.$this->tag);

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
