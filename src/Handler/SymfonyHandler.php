<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Domain;
use App\Entity\Installation;
use App\Entity\Site;
use App\Types\DetectionType;
use App\Types\FrameworkTypes;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler for DetectionResult off type "symfony".
 */
class SymfonyHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     */
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly ValidatorInterface $validator)
    {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        $installationRepository = $this->entityManager->getRepository(Installation::class);

        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $installation = $installationRepository->findByRootDirAndServer($detectionResult->getRootDir(), $detectionResult->getServer());

            if (isset($data->composerVersion)) {
                $installation?->setComposerVersion($data->composerVersion);
            }
            if (isset($data->symfony->version)) {
                $installation?->setFrameworkVersion($data->symfony->version);
            }
            if (isset($data->symfony->eof)) {
                $installation?->setEof($data->symfony->eof);
            }
            if (isset($data->symfony->lts)) {
                $installation?->setLts($data->symfony->lts);
            }
            if (isset($data->symfony->phpVersion)) {
                $installation?->setPhpVersion($data->symfony->phpVersion);
            }
            $installation?->setType(FrameworkTypes::SYMFONY);

            $this->entityManager->flush();
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::SYMFONY === $type;
    }
}
