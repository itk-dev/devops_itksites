<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\MappedSuperclass]
class AbstractHandlerResult extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $rootDir;

    #[ORM\ManyToOne(targetEntity: Server::class, inversedBy: 'installations')]
    #[ORM\JoinColumn(nullable: false)]
    private Server $server;

    #[ORM\ManyToOne(targetEntity: DetectionResult::class, cascade: ['persist', 'remove'], fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private DetectionResult $detectionResult;

    public function getRootDir(): string
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

    public function getDetectionResult(): ?DetectionResult
    {
        return $this->detectionResult;
    }

    public function setDetectionResult(DetectionResult $detectionResult): self
    {
        $this->detectionResult = $detectionResult;

        $this->rootDir = $detectionResult->getRootDir();
        $this->server = $detectionResult->getServer();

        return $this;
    }
}
