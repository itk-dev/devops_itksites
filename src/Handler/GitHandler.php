<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Git;
//use App\Entity\Installation;
//use App\Entity\Site;
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
            if (isset($data->remotes)) {
                switch (count($data->remotes)) {
                    case 0:
                        $git->setRemote('');
                        break;
                    case 1:
                        $git->setRemote($data->remotes[0]);
                        break;
                    default:
                        1;
                        // @TODO sker det?
                }
            }
            if (isset($data->tag)) {
                $git->setTag($data->tag);
            }
            if (isset($data->changes)) {
                $git->setChanges(join("\n", $data->changes));
            }
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }

        $this->entityManager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function supportsType(string $type): bool
    {
        return DetectionType::GIT === $type;
    }


}
