<?php

namespace App\Entity;

use App\Repository\ServerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServerRepository::class)]
class Server extends AbstractBaseEntity implements UserInterface
{
    private const ROLES = ['ROLE_USER', 'ROLE_SERVER'];

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    #[Assert\Length(
        min: 40,
        max: 255,
        minMessage: 'Api key must be at least {{ limit }} characters long',
        maxMessage: 'Api key cannot be longer than {{ limit }} characters',
    )]
    private string $apiKey;

    #[ORM\Column(type: 'string', length: 255, unique: true)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $hostingProviderName;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    #[Assert\Ip]
    private ?string $internalIp;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    #[Assert\Ip]
    private ?string $externalIp;

    #[ORM\Column(type: 'boolean')]
    private bool $aarhusSsl = false;

    #[ORM\Column(type: 'boolean')]
    private bool $letsEncryptSsl = false;

    #[ORM\Column(type: 'boolean')]
    private bool $veeam = false;

    #[ORM\Column(type: 'boolean')]
    private bool $azureBackup = false;

    #[ORM\Column(type: 'boolean')]
    private bool $monitoring = false;

    #[ORM\Column(type: 'string', length: 5, nullable: true)]
    private $databaseVersion;

    #[ORM\Column(type: 'string', length: 15)]
    private string $system;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $note;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $serviceDeskTicket;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $usedFor;

    #[ORM\OneToMany(mappedBy: 'server', targetEntity: DetectionResult::class, orphanRemoval: true)]
    private Collection $detectionResults;

    #[ORM\Column(type: 'string', length: 25)]
    private string $hostingProvider;

    #[ORM\OneToMany(mappedBy: 'server', targetEntity: Installation::class, orphanRemoval: true)]
    private Collection $installations;

    #[ORM\Column(type: 'string', length: 10)]
    private string $type;

    /**
     * @throws \Exception
     */
    public function __construct()
    {
        $this->detectionResults = new ArrayCollection();
        $this->apiKey = sha1(\random_bytes(40));
        $this->installations = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getName();
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

    public function getDatabaseVersion(): ?string
    {
        return $this->databaseVersion;
    }

    public function setDatabaseVersion(?string $databaseVersion): self
    {
        $this->databaseVersion = $databaseVersion;

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

    public function getApiKey(): string
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

    public function getAarhusSsl(): ?bool
    {
        return $this->aarhusSsl;
    }

    public function setAarhusSsl(bool $aarhusSsl): self
    {
        $this->aarhusSsl = $aarhusSsl;

        return $this;
    }

    public function getLetsEncryptSsl(): ?bool
    {
        return $this->letsEncryptSsl;
    }

    public function setLetsEncryptSsl(bool $letsEncryptSsl): self
    {
        $this->letsEncryptSsl = $letsEncryptSsl;

        return $this;
    }

    public function getHostingProvider(): ?string
    {
        return $this->hostingProvider;
    }

    public function setHostingProvider(string $hostingProvider): self
    {
        $this->hostingProvider = $hostingProvider;

        return $this;
    }

    /**
     * @return Collection<int, Installation>
     */
    public function getInstallations(): Collection
    {
        return $this->installations;
    }

    public function setInstallations(Collection $collection): self
    {
        foreach ($this->installations as $installation) {
            if (!$collection->contains($installation)) {
                $this->removeInstallation($installation);
            }
        }

        foreach ($collection as $item) {
            $this->addInstallation($item);
        }

        return $this;
    }

    public function addInstallation(Installation $installation): self
    {
        if (!$this->installations->contains($installation)) {
            $this->installations[] = $installation;
            $installation->setServer($this);
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): self
    {
        if ($this->installations->removeElement($installation)) {
            // set the owning side to null (unless already changed)
            if ($installation->getServer() === $this) {
                $installation->setServer(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }
}
