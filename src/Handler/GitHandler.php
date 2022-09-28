<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Git;
use App\Entity\GitRemote;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "git".
 */
class GitHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $gitRepository = $this->entityManager->getRepository(Git::class);
        $gitRemoteRepository = $this->entityManager->getRepository(GitRemote::class);

        $git = $gitRepository->findOneBy([
            'rootDir' => $detectionResult->getRootDir(),
            'server' => $detectionResult->getServer(),
        ]);

        if (null === $git) {
            $git = new Git();
            $this->entityManager->persist($git);
        }

        $git->setDetectionResult($detectionResult);

        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $git->clearRemotes();
            $this->entityManager->flush();
            foreach ($data->remotes as $remote) {
                $this->entityManager->flush();
                $gitRemote = $gitRemoteRepository->findOneBy([
                    'url' => $remote,
                ]);

                if (null === $gitRemote) {
                    $gitRemote = new GitRemote($remote);
                    try {
                        $this->entityManager->persist($gitRemote);
                        $gitRemote->setUrl($remote);
                    } catch (\Doctrine\DBAL\Exception\UniqueConstraintViolationException $e) {
                        var_dump($e);
                        continue;
                    } catch (\Exception $e) {
                        var_dump($e);
                    }
                }
                $git->addRemote($gitRemote);
            }
            $tag = (isset($data->tag) && '' !== $data->tag) ? $data->tag : 'unknown';
            $git->setTag($tag);
            if (isset($data->changes)) {
                $git->setChanges(join("\n", $data->changes));
                $git->setChangesCount(count($data->changes));
            }
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }
    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::GIT === $type;
    }
}
