<?php

namespace App\Repository;

use App\Entity\ModuleVersion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModuleVersion>
 *
 * @method ModuleVersion|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModuleVersion|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModuleVersion[]    findAll()
 * @method ModuleVersion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleVersionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModuleVersion::class);
    }
}
