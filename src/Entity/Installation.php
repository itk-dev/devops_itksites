<?php

namespace App\Entity;

use App\Repository\InstallationRepository;
use App\Types\FrameworkTypes;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstallationRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootdir_idx', fields: ['server', 'rootDir'])]
class Installation extends AbstractHandlerResult
{
    #[ORM\OneToMany(mappedBy: 'installation', targetEntity: Site::class)]
    private Collection $sites;

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private string $type = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private string $phpVersion = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private string $composerVersion = 'unknown';

    #[ORM\Column(type: 'string', length: 10, nullable: true)]
    private string $frameworkVersion = FrameworkTypes::UNKNOWN;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
    }

    public function __toString(): string
    {
        return $this->getServer().$this->getRootDir();
    }

    /**
     * @return Collection<int, Site>
     */
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
}
