<?php

namespace App\Repository;

use App\Entity\Detection;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Detection|null find($id, $lockMode = null, $lockVersion = null)
 * @method Detection|null findOneBy(array $criteria, array $orderBy = null)
 * @method Detection[]    findAll()
 * @method Detection[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetectionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Detection::class);
    }

    // /**
    //  * @return Detection[] Returns an array of Detection objects
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
    public function findOneBySomeField($value): ?Detection
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
