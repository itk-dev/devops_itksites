<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\AdvisoryAssessmentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A developer's assessment that an advisory does not affect a git repo, in
 * VEX terms "not affected". It covers every checkout of the repo.
 *
 * Manually maintained data: the repo and advisory are stored by name, not by
 * relation, because git repos and advisories are derived data that
 * app:data:truncate-derived wipes and a replay recreates with new ids.
 */
#[ORM\Entity(repositoryClass: AdvisoryAssessmentRepository::class)]
#[ORM\UniqueConstraint(name: 'repo_advisory', fields: ['repoProvider', 'repoOrganization', 'repoName', 'advisoryId'])]
class AdvisoryAssessment extends AbstractBaseEntity implements \Stringable
{
    #[ORM\Column(length: 255)]
    private string $repoProvider = '';

    #[ORM\Column(length: 255)]
    private string $repoOrganization = '';

    #[ORM\Column(length: 255)]
    private string $repoName = '';

    #[ORM\Column(length: 255)]
    private string $advisoryId = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $reason = '';

    #[ORM\Column(length: 180)]
    private string $assessedBy = '';

    #[\Override]
    public function __toString(): string
    {
        return $this->repoOrganization.'/'.$this->repoName.': '.$this->advisoryId;
    }

    public function setGitRepo(GitRepo $gitRepo): self
    {
        $this->repoProvider = (string) $gitRepo->getProvider();
        $this->repoOrganization = (string) $gitRepo->getOrganization();
        $this->repoName = (string) $gitRepo->getRepo();

        return $this;
    }

    public function getRepoProvider(): string
    {
        return $this->repoProvider;
    }

    public function getRepoOrganization(): string
    {
        return $this->repoOrganization;
    }

    public function getRepoName(): string
    {
        return $this->repoName;
    }

    public function getAdvisoryId(): string
    {
        return $this->advisoryId;
    }

    public function setAdvisoryId(string $advisoryId): self
    {
        $this->advisoryId = $advisoryId;

        return $this;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function setReason(string $reason): self
    {
        $this->reason = $reason;

        return $this;
    }

    public function getAssessedBy(): string
    {
        return $this->assessedBy;
    }

    public function setAssessedBy(string $assessedBy): self
    {
        $this->assessedBy = $assessedBy;

        return $this;
    }
}
