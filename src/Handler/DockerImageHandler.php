<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\DockerImageTagFactory;
use App\Service\DomainFactory;
use App\Service\InstallationFactory;
use App\Service\SiteFactory;
use App\Types\DetectionType;
use App\Types\SiteType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler for DetectionResult off type "symfony".
 */
class DockerImageHandler implements DetectionResultHandlerInterface
{
    private const PHP_CONTAINER = 'phpfpm';

    /**
     * DirectoryHandler constructor.
     *
     * @param EntityManagerInterface $entityManager
     * @param DockerImageTagFactory $dockerImageTagFactory
     * @param InstallationFactory $installationFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DockerImageTagFactory $dockerImageTagFactory,
        private readonly InstallationFactory $installationFactory,
        private readonly SiteFactory $siteFactory,
        private readonly DomainFactory $domainFactory,
        private readonly ValidatorInterface $validator
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        if (empty($detectionResult->getData())) {
            return;
        }

        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            $site = $this->siteFactory->getSite('', $detectionResult);
            $site->setDetectionResult($detectionResult);
            $site->setType(SiteType::DOCKER);
            $site->setPhpVersion($this->getPhpVersionFromContainers($data->containers));
            $site->setConfigFilePath('');

            $domains = $this->domainFactory->getDomains($data->domain, $site, $detectionResult);
            $site->setDomains($domains);

            $installation = $this->installationFactory->getInstallation($detectionResult);
            $this->dockerImageTagFactory->setDockerImageTags($installation, $data->containers);

            $this->entityManager->flush();
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::DOCKER === $type;
    }

    private function getPhpVersionFromContainers(array $containers): string
    {
        foreach ($containers as $container) {
            if (self::PHP_CONTAINER === $container->name) {
                $matches = [];
                \preg_match('/\d.+\d/', $container->image, $matches);
            }
        }

        return $matches[0] ?? 'unknown';
    }
}
