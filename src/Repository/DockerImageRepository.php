<?php

namespace App\Repository;

use App\Entity\DockerImage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DockerImage>
 *
 * @method DockerImage|null find($id, $lockMode = null, $lockVersion = null)
 * @method DockerImage|null findOneBy(array $criteria, array $orderBy = null)
 * @method DockerImage[]    findAll()
 * @method DockerImage[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DockerImageRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DockerImage::class);
    }
}
