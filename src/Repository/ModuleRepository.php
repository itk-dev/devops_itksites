<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Module;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Module>
 *
 * @method Module|null find($id, $lockMode = null, $lockVersion = null)
 * @method Module|null findOneBy(array $criteria, array $orderBy = null)
 * @method Module[]    findAll()
 * @method Module[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModuleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Module::class);
    }

    /**
     * Modules of a drupal.org project. Modules stored before the project was
     * recorded match by name, as a project's main module is named after it.
     *
     * @return list<Module>
     */
    public function findByDrupalProject(string $project): array
    {
        return $this->createQueryBuilder('m')
            ->where('m.project = :project')
            ->orWhere('m.project IS NULL AND m.name = :project')
            ->setParameter('project', $project)
            ->getQuery()
            ->getResult();
    }
}
