<?php

namespace App\Repository;

use App\Entity\DetectionResult;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DetectionResult|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetectionResult|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetectionResult[]    findAll()
 * @method DetectionResult[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetectionResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetectionResult::class);
    }

    // /**
    //  * @return DetectionResult[] Returns an array of DetectionResult objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DetectionResult
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
