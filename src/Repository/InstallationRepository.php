<?php

namespace App\Repository;

use App\Entity\Installation;
use App\Entity\Server;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Installation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Installation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Installation[]    findAll()
 * @method Installation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InstallationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Installation::class);
    }

    public function findByRootDirAndServer(string $rootDir, Server $server): ?Installation
    {
        return $this->createQueryBuilder('i')
            ->andWhere(':rootDir LIKE CONCAT(i.rootDir, \'/%\') OR :rootDir = i.rootDir')
            ->setParameter('rootDir', $rootDir)
            ->andWhere('i.server = :server')
            ->setParameter('server', $server->getId()->toBinary())
            ->getQuery()
            ->getOneOrNullResult()
            ;
    }

    // /**
    //  * @return Installation[] Returns an array of Installation objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Installation
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
