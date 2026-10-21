<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AdvisoryAssessment;
use App\Entity\GitRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<AdvisoryAssessment>
 *
 * @method AdvisoryAssessment|null find($id, $lockMode = null, $lockVersion = null)
 * @method AdvisoryAssessment|null findOneBy(array $criteria, array $orderBy = null)
 * @method AdvisoryAssessment[]    findAll()
 * @method AdvisoryAssessment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvisoryAssessmentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdvisoryAssessment::class);
    }

    /**
     * The repo's assessments, keyed by advisory id.
     *
     * @return array<string, AdvisoryAssessment>
     */
    public function findByGitRepo(GitRepo $gitRepo): array
    {
        $assessments = [];
        foreach ($this->findBy([
            'repoProvider' => $gitRepo->getProvider(),
            'repoOrganization' => $gitRepo->getOrganization(),
            'repoName' => $gitRepo->getRepo(),
        ]) as $assessment) {
            $assessments[$assessment->getAdvisoryId()] = $assessment;
        }

        return $assessments;
    }
}
