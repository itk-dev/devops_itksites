<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DeployResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeployResult>
 *
 * @method DeployResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeployResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeployResult[]    findAll()
 * @method DeployResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeployResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeployResult::class);
    }
}
