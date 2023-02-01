<?php

namespace App\Repository;

use App\Entity\PackageVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PackageVersion>
 *
 * @method PackageVersion|null find($id, $lockMode = null, $lockVersion = null)
 * @method PackageVersion|null findOneBy(array $criteria, array $orderBy = null)
 * @method PackageVersion[]    findAll()
 * @method PackageVersion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PackageVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PackageVersion::class);
    }
}
