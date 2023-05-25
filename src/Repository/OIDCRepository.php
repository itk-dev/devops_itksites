<?php

namespace App\Repository;

use App\Entity\OIDC;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<OIDC>
 *
 * @method OIDC|null find($id, $lockMode = null, $lockVersion = null)
 * @method OIDC|null findOneBy(array $criteria, array $orderBy = null)
 * @method OIDC[]    findAll()
 * @method OIDC[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OIDCRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OIDC::class);
    }

    public function save(OIDC $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(OIDC $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
