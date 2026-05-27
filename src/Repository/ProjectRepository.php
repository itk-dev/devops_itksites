<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GitRepo;
use App\Entity\Project;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Project>
 *
 * @method Project|null find($id, $lockMode = null, $lockVersion = null)
 * @method Project|null findOneBy(array $criteria, array $orderBy = null)
 * @method Project[]    findAll()
 * @method Project[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }

    /**
     * Projects that link to the given repo, with codeOwners and serviceAgreements eager-loaded.
     *
     * @return list<Project>
     */
    public function findByGitRepo(GitRepo $repo): array
    {
        /** @var list<Project> $projects */
        $projects = $this->createQueryBuilder('p')
            ->select('p', 'co', 'sc')
            ->leftJoin('p.codeOwners', 'co')
            ->leftJoin('p.serviceAgreements', 'sc')
            ->innerJoin('p.gitRepos', 'gr')
            ->where('gr = :repo')
            ->setParameter('repo', $repo)
            ->orderBy('p.name', 'ASC')
            ->getQuery()
            ->getResult();

        return $projects;
    }
}
