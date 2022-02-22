<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use App\Entity\DetectionResult;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;

final class DetectionResultDataPersister implements ContextAwareDataPersisterInterface, ResumableDataPersisterInterface
{
    public function __construct(private EntityManagerInterface $entityManager, private Security $security)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof DetectionResult;
    }

    /**
     * {@inheritDoc}
     *
     * @return object
     */
    public function persist($data, array $context = [])
    {
        $server = $this->security->getUser();
        $data->setServer($server);
        $this->entityManager->persist($data);
        $this->entityManager->flush();

        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($data, array $context = [])
    {
        $this->entityManager->remove($data);
        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function resumable(array $context = []): bool
    {
        return true;
    }
}
