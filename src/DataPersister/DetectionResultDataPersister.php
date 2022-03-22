<?php

namespace App\DataPersister;

use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use App\Entity\DetectionResult;
use App\Entity\Server;
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
     */
    public function persist($data, array $context = []): object
    {
        $server = $this->security->getUser();

        if ($server instanceof Server && $data instanceof DetectionResult) {
            $data->setServer($server);

            $hash = $data->generateHash()->getHash();
            $result = $this->entityManager->getRepository(DetectionResult::class)->findOneBy(['server' => $server, 'hash' => $hash]);

            if (null === $result) {
                $this->entityManager->persist($data);
                $data->setLastContact();
            } else {
                $result->setLastContact();
            }

            $this->entityManager->flush();
        }

        return $result ?? $data;
    }

    /**
     * {@inheritDoc}
     */
    public function remove($data, array $context = []): void
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
