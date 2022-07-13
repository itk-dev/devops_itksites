<?php

namespace App\Repository;

use App\Entity\GitRemote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitRemote>
 *
 * @method GitRemote|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitRemote|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitRemote[]    findAll()
 * @method GitRemote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitRemoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitRemote::class);
    }

    public function add(GitRemote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GitRemote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
