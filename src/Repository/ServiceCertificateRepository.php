<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\ServiceCertificate;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ServiceCertificate>
 *
 * @method ServiceCertificate|null find($id, $lockMode = null, $lockVersion = null)
 * @method ServiceCertificate|null findOneBy(array $criteria, array $orderBy = null)
 * @method ServiceCertificate[]    findAll()
 * @method ServiceCertificate[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ServiceCertificateRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ServiceCertificate::class);
    }

    public function save(ServiceCertificate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ServiceCertificate $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
