<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\ProjectRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProjectRepository::class)]
class Project extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(unique: true)]
    private ?int $economicsId = null;

    #[ORM\Column(length: 255)]
    private string $name = '';

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $leantimeId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $leantimeUrl = null;

    /**
     * @var Collection<int, CodeOwner>
     */
    #[ORM\ManyToMany(targetEntity: CodeOwner::class, inversedBy: 'projects', cascade: ['persist'])]
    #[ORM\JoinTable(name: 'project_code_owner')]
    private Collection $codeOwners;

    /**
     * @var Collection<int, GitRepo>
     */
    #[ORM\ManyToMany(targetEntity: GitRepo::class)]
    #[ORM\JoinTable(name: 'project_git_repo')]
    private Collection $gitRepos;

    /**
     * @var Collection<int, SecurityContract>
     */
    #[ORM\OneToMany(targetEntity: SecurityContract::class, mappedBy: 'project', cascade: ['persist'])]
    private Collection $serviceAgreements;

    public function __construct()
    {
        $this->codeOwners = new ArrayCollection();
        $this->gitRepos = new ArrayCollection();
        $this->serviceAgreements = new ArrayCollection();
    }

    #[\Override]
    public function __toString(): string
    {
        return $this->name;
    }

    public function getEconomicsId(): ?int
    {
        return $this->economicsId;
    }

    public function setEconomicsId(int $economicsId): static
    {
        $this->economicsId = $economicsId;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getLeantimeId(): ?string
    {
        return $this->leantimeId;
    }

    public function setLeantimeId(?string $leantimeId): static
    {
        $this->leantimeId = $leantimeId;

        return $this;
    }

    public function getLeantimeUrl(): ?string
    {
        return $this->leantimeUrl;
    }

    public function setLeantimeUrl(?string $leantimeUrl): static
    {
        $this->leantimeUrl = $leantimeUrl;

        return $this;
    }

    /**
     * @return Collection<int, CodeOwner>
     */
    public function getCodeOwners(): Collection
    {
        return $this->codeOwners;
    }

    public function addCodeOwner(CodeOwner $codeOwner): static
    {
        if (!$this->codeOwners->contains($codeOwner)) {
            $this->codeOwners->add($codeOwner);
        }

        return $this;
    }

    public function removeCodeOwner(CodeOwner $codeOwner): static
    {
        $this->codeOwners->removeElement($codeOwner);

        return $this;
    }

    /**
     * @return Collection<int, GitRepo>
     */
    public function getGitRepos(): Collection
    {
        return $this->gitRepos;
    }

    public function addGitRepo(GitRepo $gitRepo): static
    {
        if (!$this->gitRepos->contains($gitRepo)) {
            $this->gitRepos->add($gitRepo);
        }

        return $this;
    }

    public function removeGitRepo(GitRepo $gitRepo): static
    {
        $this->gitRepos->removeElement($gitRepo);

        return $this;
    }

    /**
     * @return Collection<int, SecurityContract>
     */
    public function getServiceAgreements(): Collection
    {
        return $this->serviceAgreements;
    }

    public function addServiceAgreement(SecurityContract $serviceAgreement): static
    {
        if (!$this->serviceAgreements->contains($serviceAgreement)) {
            $this->serviceAgreements->add($serviceAgreement);
            $serviceAgreement->setProject($this);
        }

        return $this;
    }

    public function removeServiceAgreement(SecurityContract $serviceAgreement): static
    {
        if ($this->serviceAgreements->removeElement($serviceAgreement)) {
            if ($serviceAgreement->getProject() === $this) {
                $serviceAgreement->setProject(null);
            }
        }

        return $this;
    }
}
