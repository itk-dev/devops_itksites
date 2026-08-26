<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\CodeOwner;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<CodeOwner>
 *
 * @method CodeOwner|null find($id, $lockMode = null, $lockVersion = null)
 * @method CodeOwner|null findOneBy(array $criteria, array $orderBy = null)
 * @method CodeOwner[]    findAll()
 * @method CodeOwner[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CodeOwnerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CodeOwner::class);
    }
}
