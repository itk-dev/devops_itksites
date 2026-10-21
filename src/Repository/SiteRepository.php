<?php

declare(strict_types=1);

namespace App\Repository;

use App\Admin\AdvisoryCountSort;
use App\Entity\Site;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 *
 * @extends ServiceEntityRepository<Site>
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    /**
     * Get unique primary domains from existing sites.
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

    /**
     * The number of sites with advisories not marked as not affected.
     */
    public function countWithOpenAdvisories(): int
    {
        return (int) $this->createQueryBuilder('s')
            ->select('COUNT(s.id)')
            ->where(sprintf('(%s) > 0', AdvisoryCountSort::subQuery('s')))
            ->getQuery()
            ->getSingleScalarResult();
    }
}
