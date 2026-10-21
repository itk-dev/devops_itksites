<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Advisory;
use App\Entity\AdvisoryAssessment;
use App\Entity\GitRepo;
use App\Entity\Site;
use App\Repository\AdvisoryAssessmentRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Marks a site's advisories as not affected. A mark belongs to the site's git
 * repo, so it covers every checkout of the repo, and sites without a repo
 * cannot be marked.
 */
final readonly class AdvisoryAssessor
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AdvisoryAssessmentRepository $repository,
    ) {
    }

    public function gitRepo(Site $site): ?GitRepo
    {
        return $site->getInstallation()->getGitTag()?->getRepo();
    }

    /**
     * The site's advisories, each with its assessment if it is marked.
     *
     * @return list<array{advisory: Advisory, assessment: AdvisoryAssessment|null}>
     */
    public function advisories(Site $site): array
    {
        $gitRepo = $this->gitRepo($site);
        $assessments = null === $gitRepo ? [] : $this->repository->findByGitRepo($gitRepo);

        return array_values(array_map(
            static fn (Advisory $advisory): array => ['advisory' => $advisory, 'assessment' => $assessments[$advisory->getAdvisoryId()] ?? null],
            $site->getAdvisories()->toArray(),
        ));
    }

    /**
     * Advisories that are not marked as not affected.
     */
    public function openCount(Site $site): int
    {
        return count(array_filter($this->advisories($site), static fn (array $row): bool => null === $row['assessment']));
    }

    /**
     * Mark those of the advisories that are open on the site.
     *
     * @param list<string> $advisoryIds
     *
     * @return int the number of advisories marked
     *
     * @throws \InvalidArgumentException when the site has no git repo or the reason is empty
     */
    public function markNotAffected(Site $site, array $advisoryIds, string $reason, string $assessedBy): int
    {
        $gitRepo = $this->gitRepo($site) ?? throw new \InvalidArgumentException('No git repo linked, so advisories cannot be marked as not affected.');
        $reason = trim($reason);
        if ('' === $reason) {
            throw new \InvalidArgumentException('A reason is required.');
        }

        $marked = 0;
        foreach ($this->advisories($site) as ['advisory' => $advisory, 'assessment' => $assessment]) {
            if (null !== $assessment || !in_array($advisory->getAdvisoryId(), $advisoryIds, true)) {
                continue;
            }
            $this->entityManager->persist(new AdvisoryAssessment()
                ->setGitRepo($gitRepo)
                ->setAdvisoryId($advisory->getAdvisoryId())
                ->setReason($reason)
                ->setAssessedBy($assessedBy));
            ++$marked;
        }
        $this->entityManager->flush();

        return $marked;
    }

    public function removeMark(Site $site, string $advisoryId): void
    {
        $gitRepo = $this->gitRepo($site);
        $assessment = null === $gitRepo ? null : ($this->repository->findByGitRepo($gitRepo)[$advisoryId] ?? null);
        if (null !== $assessment) {
            $this->entityManager->remove($assessment);
            $this->entityManager->flush();
        }
    }
}
