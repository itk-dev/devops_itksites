<?php

namespace App\Repository;

use App\Entity\GitTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitTag>
 *
 * @method GitTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitTag[]    findAll()
 * @method GitTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitTag::class);
    }
}
