<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
class Server extends AbstractBaseEntity implements UserInterface
{
    private const ROLES = ['ROLE_USER', 'ROLE_SERVER'];

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private $apiKey;

    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $hostingProviderName;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private ?string $internalIp;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private ?string $externalIp;

    #[ORM\Column(type: 'boolean')]
    private ?bool $veeam;

    #[ORM\Column(type: 'boolean')]
    private $azureBackup;

    #[ORM\Column(type: 'boolean')]
    private $monitoring;

    #[ORM\Column(type: 'string', length: 15)]
    private $sshUser;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private $databaseVersion;

    #[ORM\Column(type: 'string', length: 15)]
    private $sslProvider;

    #[ORM\Column(type: 'string', length: 15)]
    private $system;

    #[ORM\Column(type: 'text', nullable: true)]
    private $note;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $serviceDeskTicket;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private $usedFor;

    #[ORM\OneToMany(mappedBy: 'server', targetEntity: DetectionResult::class, orphanRemoval: true)]
    private $detectionResults;

    public function __construct()
    {
        $this->detectionResults = new ArrayCollection();
    }

    public function getRoles(): array
    {
        return self::ROLES;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getUserIdentifier(): string
    {
        return $this->getApiKey();
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getHostingProviderName(): ?string
    {
        return $this->hostingProviderName;
    }

    public function setHostingProviderName(?string $hostingProviderName): self
    {
        $this->hostingProviderName = $hostingProviderName;

        return $this;
    }

    public function getInternalIp(): ?string
    {
        return $this->internalIp;
    }

    public function setInternalIp(?string $internalIp): self
    {
        $this->internalIp = $internalIp;

        return $this;
    }

    public function getExternalIp(): ?string
    {
        return $this->externalIp;
    }

    public function setExternalIp(?string $externalIp): self
    {
        $this->externalIp = $externalIp;

        return $this;
    }

    public function getVeeam(): ?bool
    {
        return $this->veeam;
    }

    public function setVeeam(bool $veeam): self
    {
        $this->veeam = $veeam;

        return $this;
    }

    public function getAzureBackup(): ?bool
    {
        return $this->azureBackup;
    }

    public function setAzureBackup(bool $azureBackup): self
    {
        $this->azureBackup = $azureBackup;

        return $this;
    }

    public function getMonitoring(): ?bool
    {
        return $this->monitoring;
    }

    public function setMonitoring(bool $monitoring): self
    {
        $this->monitoring = $monitoring;

        return $this;
    }

    public function getSshUser(): ?string
    {
        return $this->sshUser;
    }

    public function setSshUser(string $sshUser): self
    {
        $this->sshUser = $sshUser;

        return $this;
    }

    public function getDatabaseVersion(): ?string
    {
        return $this->databaseVersion;
    }

    public function setDatabaseVersion(?string $databaseVersion): self
    {
        $this->databaseVersion = $databaseVersion;

        return $this;
    }

    public function getSslProvider(): ?string
    {
        return $this->sslProvider;
    }

    public function setSslProvider(string $sslProvider): self
    {
        $this->sslProvider = $sslProvider;

        return $this;
    }

    public function getSystem(): ?string
    {
        return $this->system;
    }

    public function setSystem(string $system): self
    {
        $this->system = $system;

        return $this;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function setNote(?string $note): self
    {
        $this->note = $note;

        return $this;
    }

    public function getServiceDeskTicket(): ?string
    {
        return $this->serviceDeskTicket;
    }

    public function setServiceDeskTicket(string $serviceDeskTicket): self
    {
        $this->serviceDeskTicket = $serviceDeskTicket;

        return $this;
    }

    public function getUsedFor(): ?string
    {
        return $this->usedFor;
    }

    public function setUsedFor(?string $usedFor): self
    {
        $this->usedFor = $usedFor;

        return $this;
    }

    public function getApiKey(): ?string
    {
        return $this->apiKey;
    }

    public function setApiKey(string $apiKey): self
    {
        $this->apiKey = $apiKey;

        return $this;
    }

    /**
     * @return Collection<int, DetectionResult>
     */
    public function getDetectionResults(): Collection
    {
        return $this->detectionResults;
    }

    public function addDetectionResult(DetectionResult $detectionResult): self
    {
        if (!$this->detectionResults->contains($detectionResult)) {
            $this->detectionResults[] = $detectionResult;
            $detectionResult->setServer($this);
        }

        return $this;
    }

    public function removeDetectionResult(DetectionResult $detectionResult): self
    {
        if ($this->detectionResults->removeElement($detectionResult)) {
            // set the owning side to null (unless already changed)
            if ($detectionResult->getServer() === $this) {
                $detectionResult->setServer(null);
            }
        }

        return $this;
    }
}
