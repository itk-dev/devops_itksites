<?php

namespace App\Entity;

use App\Repository\InstallationRepository;
use App\Types\FrameworkTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallationRepository::class)]
#[ORM\AssociationOverrides([
    new ORM\AssociationOverride('server', [
        'joinColumns' => new ORM\JoinColumn(
            nullable: false, onDelete: 'CASCADE'
        )],
        inversedBy: 'installations',
    ),
])]
#[ORM\UniqueConstraint(name: 'server_rootdir_idx', fields: ['server', 'rootDir'])]
class Installation extends AbstractHandlerResult
{
    #[ORM\OneToMany(mappedBy: 'installation', targetEntity: Site::class)]
    private Collection $sites;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $type = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $phpVersion = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $composerVersion = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private ?string $frameworkVersion = FrameworkTypes::UNKNOWN;

    #[ORM\Column(type: 'boolean')]
    private bool $lts = false;

    #[ORM\Column(type: 'string', length: 30)]
    private string $eol = '';

    #[ORM\Column(type: 'text')]
    private string $gitChanges = '';

    #[ORM\Column(type: 'integer')]
    private int $gitChangesCount = 0;

    #[ORM\Column(length: 10)]
    private ?string $gitClonedScheme = '';

    #[ORM\ManyToMany(targetEntity: PackageVersion::class, inversedBy: 'installations', cascade: ['persist'])]
    private Collection $packageVersions;

    #[ORM\ManyToMany(targetEntity: ModuleVersion::class, inversedBy: 'installations', cascade: ['persist'])]
    private Collection $moduleVersions;

    #[ORM\ManyToMany(targetEntity: DockerImageTag::class, inversedBy: 'installations', cascade: ['persist'])]
    private Collection $dockerImageTags;

    #[ORM\ManyToOne(cascade: ['persist'], inversedBy: 'installations')]
    private ?GitTag $gitTag = null;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->packageVersions = new ArrayCollection();
        $this->moduleVersions = new ArrayCollection();
        $this->dockerImageTags = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getServer().$this->getRootDir();
    }

    public function getSites(): Collection
    {
        return $this->sites;
    }

    public function addSite(Site $site): self
    {
        if (!$this->sites->contains($site)) {
            $this->sites[] = $site;
            $site->setInstallation($this);
        }

        return $this;
    }

    public function removeSite(Site $site): self
    {
        if ($this->sites->removeElement($site)) {
            // set the owning side to null (unless already changed)
            if ($site->getInstallation() === $this) {
                $site->setInstallation(null);
            }
        }

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPhpVersion(): ?string
    {
        return $this->phpVersion;
    }

    public function setPhpVersion(?string $phpVersion): self
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    public function getComposerVersion(): ?string
    {
        return $this->composerVersion;
    }

    public function setComposerVersion(?string $composerVersion): self
    {
        $this->composerVersion = $composerVersion;

        return $this;
    }

    public function getFrameworkVersion(): ?string
    {
        return $this->frameworkVersion;
    }

    public function setFrameworkVersion(?string $frameworkVersion): self
    {
        $this->frameworkVersion = $frameworkVersion;

        return $this;
    }

    public function getDomain(): ?string
    {
        if ($this->sites->count() > 0) {
            return $this->sites->first()->getPrimaryDomain();
        }

        return null;
    }

    public function isLts(): ?bool
    {
        return $this->lts;
    }

    public function setLts(bool $lts): self
    {
        $this->lts = $lts;

        return $this;
    }

    public function getEol(): ?string
    {
        return $this->eol;
    }

    public function setEol(string $eol): self
    {
        $this->eol = $eol;

        return $this;
    }

    public function getPackageVersions(): Collection
    {
        return $this->packageVersions;
    }

    public function setPackageVersions(Collection $newPackageVersions): self
    {
        foreach ($this->packageVersions as $packageVersion) {
            if (!$newPackageVersions->contains($packageVersion)) {
                $packageVersion->removeInstallation($this);
                $this->packageVersions->removeElement($packageVersion);
            }
        }

        foreach ($newPackageVersions as $newPackageVersion) {
            $this->addPackageVersion($newPackageVersion);
        }

        return $this;
    }

    public function setModuleVersions(Collection $newModuleVersions): self
    {
        foreach ($this->moduleVersions as $moduleVersion) {
            if (!$newModuleVersions->contains($moduleVersion)) {
                $moduleVersion->removeInstallation($this);
                $this->moduleVersions->removeElement($moduleVersion);
            }
        }

        foreach ($newModuleVersions as $newModuleVersion) {
            $this->addModuleVersion($newModuleVersion);
        }

        return $this;
    }

    public function setDockerImageTags(Collection $newDockerImageTags): self
    {
        foreach ($this->dockerImageTags as $dockerImageTag) {
            if (!$newDockerImageTags->contains($dockerImageTag)) {
                $dockerImageTag->removeInstallation($this);
                $this->dockerImageTags->removeElement($dockerImageTag);
            }
        }

        foreach ($newDockerImageTags as $newDockerImageTag) {
            $this->addDockerImageTag($newDockerImageTag);
        }

        return $this;
    }

    public function addPackageVersion(PackageVersion $packageVersion): self
    {
        if (!$this->packageVersions->contains($packageVersion)) {
            $this->packageVersions[] = $packageVersion;
            $packageVersion->addInstallation($this);
        }

        return $this;
    }

    public function removePackageVersion(PackageVersion $packageVersion): self
    {
        if ($this->packageVersions->removeElement($packageVersion)) {
            $packageVersion->removeInstallation($this);
        }

        return $this;
    }

    public function getModuleVersions(): Collection
    {
        return $this->moduleVersions;
    }

    public function addModuleVersion(ModuleVersion $moduleVersion): self
    {
        if (!$this->moduleVersions->contains($moduleVersion)) {
            $this->moduleVersions[] = $moduleVersion;
            $moduleVersion->addInstallation($this);
        }

        return $this;
    }

    public function removeModuleVersion(ModuleVersion $moduleVersion): self
    {
        if ($this->moduleVersions->removeElement($moduleVersion)) {
            $moduleVersion->removeInstallation($this);
        }

        return $this;
    }

    public function getDockerImageTags(): Collection
    {
        return $this->dockerImageTags;
    }

    public function addDockerImageTag(DockerImageTag $dockerImageTag): self
    {
        if (!$this->dockerImageTags->contains($dockerImageTag)) {
            $this->dockerImageTags[] = $dockerImageTag;
            $dockerImageTag->addInstallation($this);
        }

        return $this;
    }

    public function removeDockerImageTag(DockerImageTag $dockerImageTag): self
    {
        if ($this->dockerImageTags->removeElement($dockerImageTag)) {
            $dockerImageTag->removeInstallation($this);
        }

        return $this;
    }

    public function getGitChanges(): string
    {
        return $this->gitChanges;
    }

    public function setGitChanges(string $gitChanges): self
    {
        $this->gitChanges = $gitChanges;

        return $this;
    }

    public function getGitChangesCount(): int
    {
        return $this->gitChangesCount;
    }

    public function setGitChangesCount(int $gitChangesCount): self
    {
        $this->gitChangesCount = $gitChangesCount;

        return $this;
    }

    public function getGitClonedScheme(): ?string
    {
        return $this->gitClonedScheme;
    }

    public function setGitClonedScheme(?string $gitClonedScheme): self
    {
        $this->gitClonedScheme = $gitClonedScheme;

        return $this;
    }

    public function getGitTag(): ?GitTag
    {
        return $this->gitTag;
    }

    public function setGitTag(?GitTag $gitTag): self
    {
        $this->gitTag = $gitTag;

        return $this;
    }
}
