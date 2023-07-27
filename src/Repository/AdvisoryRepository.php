<?php

namespace App\Repository;

use App\Entity\Advisory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Advisory>
 *
 * @method Advisory|null find($id, $lockMode = null, $lockVersion = null)
 * @method Advisory|null findOneBy(array $criteria, array $orderBy = null)
 * @method Advisory[]    findAll()
 * @method Advisory[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AdvisoryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Advisory::class);
    }

    public function save(Advisory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Advisory $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
