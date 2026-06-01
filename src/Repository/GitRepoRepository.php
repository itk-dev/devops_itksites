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

    /**
     * Distinct PackageVersion IDs reachable from each repo via
     * gitTags → installations → packageVersions, restricted to versions that
     * have at least one Advisory. This is the same chain used by
     * findReposWithAdvisoryCount(), so a filter on Advisory.packageVersions
     * with these IDs returns exactly the advisories counted on the repo list.
     *
     * Both keys and values are the Ulid's compact base32 form (i.e. its
     * default __toString) so they match the choice keys Symfony's EntityType
     * generates for filter URLs.
     *
     * @return array<string, list<string>> repoUlid (base32) => list of PackageVersion Ulid (base32)
     */
    public function findPackageVersionsPerRepoWithAdvisories(): array
    {
        /** @var list<array{repoId: \Symfony\Component\Uid\Ulid, pvId: \Symfony\Component\Uid\Ulid}> $rows */
        $rows = $this->createQueryBuilder('r')
            ->select('r.id AS repoId', 'pv.id AS pvId')
            ->distinct()
            ->innerJoin('r.gitTags', 'gt')
            ->innerJoin('gt.installations', 'i')
            ->innerJoin('i.packageVersions', 'pv')
            ->innerJoin('pv.advisories', 'a')
            ->getQuery()
            ->getResult();

        $map = [];
        foreach ($rows as $row) {
            $repoKey = (string) $row['repoId'];
            $map[$repoKey][] = (string) $row['pvId'];
        }

        return $map;
    }
}
