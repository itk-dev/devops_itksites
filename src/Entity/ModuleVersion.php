<?php

namespace App\Entity;

use App\Repository\ModuleVersionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Exception;

#[ORM\Entity(repositoryClass: ModuleVersionRepository::class)]
#[ORM\UniqueConstraint(name: 'module_version', columns: ['module_id', 'version'])]
class ModuleVersion extends AbstractBaseEntity
{
    #[ORM\ManyToOne(targetEntity: Module::class, inversedBy: 'moduleVersions')]
    #[ORM\JoinColumn(nullable: false)]
    private Module $module;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $version;

    #[ORM\ManyToMany(targetEntity: Installation::class, inversedBy: 'moduleVersions')]
    private Collection $installations;

    public function __toString(): string
    {
        return $this->getVersion();
    }

    /**
     * @throws Exception
     */
    public function display(int $style): string
    {
        switch ($style) {
            case 0:
                return $this->getVersion();
            case 1:
                return $this->getModule().':'.$this->getVersion();
        }
        throw new Exception('Unknown style');
    }

    public function __construct()
    {
        $this->installations = new ArrayCollection();
    }

    public function getModule(): Module
    {
        return $this->module;
    }

    public function setModule(Module $module): self
    {
        $this->module = $module;

        return $this;
    }

    public function getVersion(): string
    {
        return $this->version ?? 'Unknown';
    }

    public function setVersion(?string $version): self
    {
        $this->version = $version;

        return $this;
    }

    /**
     * @return Collection<int, Installation>
     */
    public function getInstallations(): Collection
    {
        return $this->installations;
    }

    public function addInstallation(Installation $installation): self
    {
        if (!$this->installations->contains($installation)) {
            $this->installations[] = $installation;
        }

        return $this;
    }

    public function removeInstallation(Installation $installation): self
    {
        $this->installations->removeElement($installation);

        return $this;
    }
}
