<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\DockerImageTag;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DockerImageTag>
 *
 * @method DockerImageTag|null find($id, $lockMode = null, $lockVersion = null)
 * @method DockerImageTag|null findOneBy(array $criteria, array $orderBy = null)
 * @method DockerImageTag[]    findAll()
 * @method DockerImageTag[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DockerImageTagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DockerImageTag::class);
    }
}
