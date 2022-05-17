<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\UniqueConstraint(name: 'server_configfilepath_idx', fields: ['server', 'configFilePath'])]
class Site extends AbstractHandlerResult
{
    #[ORM\Column(type: 'string', length: 10)]
    #[Assert\Length(
        min: 1,
        max: 10,
        minMessage: 'Your php version string cannot be empty',
        maxMessage: 'Your php version string cannot be longer than {{ limit }} characters',
    )]
    #[Assert\NotNull]
    private string $phpVersion = '';

    #[ORM\Column(type: 'string', length: 255)]
    #[Assert\Length(
        min: 1,
        max: 255,
        minMessage: 'Your config file path cannot be empty',
        maxMessage: 'Your config file path cannot be longer than {{ limit }} characters',
    )]
    #[Assert\NotNull]
    private string $configFilePath = '';

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Domain::class, cascade: ['persist'], fetch: 'EAGER', orphanRemoval: true)]
    #[Assert\Count(
        min: 1,
        minMessage: 'A site must have at least one domain'
    )]
    private Collection $domains;

    public function __construct()
    {
        $this->domains = new ArrayCollection();
    }

    public function getPhpVersion(): ?string
    {
        return $this->phpVersion;
    }

    public function setPhpVersion(string $phpVersion): self
    {
        $this->phpVersion = $phpVersion;

        return $this;
    }

    public function getConfigFilePath(): ?string
    {
        return $this->configFilePath;
    }

    public function setConfigFilePath(string $configFilePath): self
    {
        $this->configFilePath = $configFilePath;

        return $this;
    }

    /**
     * @return Collection<int, Domain>
     */
    public function getDomains(): Collection
    {
        return $this->domains;
    }

    public function addDomain(Domain $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains[] = $domain;
            $domain->setSite($this);
        }

        return $this;
    }

    public function removeDomain(Domain $domain): self
    {
        if ($this->domains->removeElement($domain)) {
            // set the owning side to null (unless already changed)
            if ($domain->getSite() === $this) {
                $domain->setSite(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->configFilePath;
    }


}
