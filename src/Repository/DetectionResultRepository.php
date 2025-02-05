<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Entity\Server;
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

    /**
     * Delete all except X similar detection results
     *
     * @param DetectionResult $detectionResult
     * @param int $keep
     * @param bool $flush
     *
     * @return void
     */
    public function cleanup(DetectionResult $detectionResult, int $keep = 5, bool $flush = false): void
    {
        if ($keep < 1) {
            return;
        }

        $em = $this->getEntityManager();

        $results = $this->findBy(
            [
                'server' => $detectionResult->getServer(),
                'type' => $detectionResult->getType(),
                'rootDir' => $detectionResult->getRootDir(),
            ],
            ['lastContact' => 'DESC'], null, $keep
        );

        foreach ($results as $result) {
            $em->remove($result);
        }

        if ($flush) {
            $em->flush();
        }
    }

    /**
     * Delete all detection results for a given installation.
     *
     * @param Installation $installation
     *
     * @return void
     */
    public function deleteByInstallation(Installation $installation): void
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb->delete(DetectionResult::class, 'd')
            ->where('d.server = :server')
            ->andWhere('d.rootDir =:rootDir')
            ->setParameter('server', $installation->getServer())
            ->setParameter('rootDir', $installation->getRootDir());
        $qb->getQuery()->execute();
    }
}
