<?php

declare(strict_types=1);

namespace App\Entity;

use App\Utils\RootDirNormalizer;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

#[ORM\MappedSuperclass]
class AbstractHandlerResult extends AbstractBaseEntity
{
    #[ORM\Column(type: 'string', length: 255)]
    private string $rootDir;

    #[ORM\ManyToOne(targetEntity: Server::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    #[Groups(['export'])]
    #[SerializedName('Server')]
    protected ?Server $server = null;

    #[ORM\ManyToOne(targetEntity: DetectionResult::class, fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private DetectionResult $detectionResult;

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

    public function getDetectionResult(): DetectionResult
    {
        return $this->detectionResult;
    }

    public function setDetectionResult(DetectionResult $detectionResult): self
    {
        $this->detectionResult = $detectionResult;

        $this->setRootDir($detectionResult->getRootDir());
        $this->setServer($detectionResult->getServer());

        return $this;
    }
}
