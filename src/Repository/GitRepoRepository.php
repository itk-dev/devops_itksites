<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\GitRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitRepo>
 *
 * @method GitRepo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitRepo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitRepo[]    findAll()
 * @method GitRepo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitRepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitRepo::class);
    }

    /**
     * Repos reachable from any package-version advisory, with their advisory count.
     *
     * Path: GitRepo → GitTag → Installation → PackageVersion → Advisory.
     *
     * @return list<array{repo: GitRepo, advisoryCount: int}>
     */
    public function findReposWithAdvisoryCount(): array
    {
        /** @var list<array{repo: GitRepo, advisoryCount: int}> $rows */
        $rows = $this->createQueryBuilder('r')
            ->select('r AS repo', 'COUNT(DISTINCT a.id) AS advisoryCount')
            ->innerJoin('r.gitTags', 'gt')
            ->innerJoin('gt.installations', 'i')
            ->innerJoin('i.packageVersions', 'pv')
            ->innerJoin('pv.advisories', 'a')
            ->groupBy('r.id')
            ->having('COUNT(DISTINCT a.id) > 0')
            ->orderBy('r.organization', 'ASC')
            ->addOrderBy('r.repo', 'ASC')
            ->getQuery()
            ->getResult();

        return array_map(
            static fn (array $row): array => [
                'repo' => $row['repo'],
                'advisoryCount' => (int) $row['advisoryCount'],
            ],
            $rows,
        );
    }
}
