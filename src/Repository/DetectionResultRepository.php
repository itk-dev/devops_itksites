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

    /**
     * Remove detection results base on last contact.
     *
     * @param \dateTime $date
     *   The date to delete before
     *
     * @return mixed
     *   Number of records removed or false
     */
    public function remove(\DateTime $date): mixed
    {
        return $this->createQueryBuilder('d')
            ->delete(DetectionResult::class, 's')
            ->andWhere('s.lastContact < (:date)')
            ->setParameter('date', $date)
            ->getQuery()
            ->getResult();
    }
}
