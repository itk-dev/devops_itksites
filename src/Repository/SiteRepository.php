<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Server;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function findByRootDirAndServer(string $rootDir, Server $server): mixed
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.rootDir LIKE :rootDir')
            ->setParameter('rootDir', $rootDir.'%')
            ->andWhere('s.server = :server')
            ->setParameter('server', $server->getId()->toBinary())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Get unique primary domains from existing sites.
     *
     * @return array
     */
    public function getPrimaryDomains(): array
    {
        return $this->createQueryBuilder('s')
            ->select('s.primaryDomain')
            ->orderBy('s.primaryDomain')
            ->distinct()
            ->getQuery()
            ->getResult(AbstractQuery::HYDRATE_SCALAR_COLUMN);
    }
}
