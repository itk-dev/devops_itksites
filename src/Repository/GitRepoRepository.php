<?php

namespace App\Repository;

use App\Entity\GitRepo;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GitTagRepository>
 *
 * @method GitRepo|null find($id, $lockMode = null, $lockVersion = null)
 * @method GitRepo|null findOneBy(array $criteria, array $orderBy = null)
 * @method GitRepo[]    findAll()
 * @method GitRepo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GitRepoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, GitRepo::class);
    }

    public function add(GitRepo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GitRepo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
