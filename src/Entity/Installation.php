<?php

namespace App\Entity;

use App\Repository\InstallationRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallationRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootdir_idx', fields: ['server', 'rootDir'])]
class Installation extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $rootDir;

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'installations')]
    #[ORM\JoinColumn(nullable: false)]
    private Server $server;

    #[ORM\OneToOne(targetEntity: DetectionResult::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $detectionResult;

    public function getRootDir(): ?string
    {
        return $this->rootDir;
    }

    public function setRootDir(string $rootDir): self
    {
        $this->rootDir = $rootDir;

        return $this;
    }

    public function getServer(): ?Server
    {
        return $this->server;
    }

    public function setServer(?Server $server): self
    {
        $this->server = $server;

        return $this;
    }

    public function getDetectionResult(): ?DetectionResult
    {
        return $this->detectionResult;
    }

    public function setDetectionResult(DetectionResult $detectionResult): self
    {
        $this->detectionResult = $detectionResult;

        return $this;
    }
}
