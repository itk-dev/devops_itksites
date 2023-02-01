<?php

namespace App\Entity;

use App\Repository\SiteRepository;
use App\Types\SiteType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: SiteRepository::class)]
#[ORM\UniqueConstraint(name: 'server_rootDir_configFilePath_idx', fields: ['server', 'rootDir', 'configFilePath'])]
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

    #[ORM\OneToMany(mappedBy: 'site', targetEntity: Domain::class, cascade: ['persist'])]
    #[Assert\Count(
        min: 1,
        minMessage: 'A site must have at least one domain'
    )]
    private Collection $domains;

    #[ORM\ManyToOne(targetEntity: Installation::class, inversedBy: 'sites')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Installation $installation;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $primaryDomain;

    #[ORM\Column(type: 'string', length: 25)]
    private string $type = '';

    public function __construct()
    {
        $this->domains = new ArrayCollection();
    }

    public function __toString(): string
    {
        if (SiteType::DOCKER === $this->type) {
            return str_replace('/data/www', '.', $this->getRootDir());
        }

        return str_replace('/etc/nginx/sites-enabled', '.', $this->configFilePath);
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

    /**
     * @param Collection<int, Domain> $newDomains
     *
     * @return $this
     */
    public function setDomains(Collection $newDomains): self
    {
        /** @var Domain $domain */
        foreach ($this->domains as $domain) {
            if (!$newDomains->contains($domain)) {
                $this->domains->removeElement($domain);
            }
        }

        /** @var Domain $newDomain */
        foreach ($newDomains as $newDomain) {
            $this->addDomain($newDomain);
        }

        return $this;
    }

    public function addDomain(Domain $domain): self
    {
        if (!$this->domains->contains($domain)) {
            $this->domains[] = $domain;
            $domain->setSite($this);
        }

        $this->updatePrimaryDomain();

        return $this;
    }

    public function getInstallation(): Installation
    {
        return $this->installation;
    }

    public function setInstallation(Installation $installation): self
    {
        $this->installation = $installation;

        return $this;
    }

    public function getPrimaryDomain(): ?string
    {
        return $this->primaryDomain;
    }

    /**
     * Update primary domain for the site.
     *
     * For sites with multiple domains we consider the domain with the
     * lowest number of subdomains to be the primary domain. E.g. for
     * these domains the first is the primary:
     * - 360.aarhuskommune.dk
     * - 360.aarhuskommune.dk.srvitkphp74.itkdev.dk
     *
     * @return void
     */
    private function updatePrimaryDomain(): void
    {
        $domainCount = count($this->domains);

        if ($domainCount >= 1) {
            $address = $this->domains->first()->getAddress();
            $segments = count(explode('.', $address));
            $this->primaryDomain = $address;

            if ($domainCount > 1) {
                foreach ($this->domains as $domain) {
                    if ($segments > count(explode('.', $domain->getAddress()))) {
                        $segments = count(explode('.', $domain->getAddress()));
                        $this->primaryDomain = $domain->getAddress();
                    }
                }
            }
        }
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
