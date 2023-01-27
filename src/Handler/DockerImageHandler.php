<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Service\DockerImageTagFactory;
use App\Service\DomainFactory;
use App\Service\InstallationFactory;
use App\Service\ModuleVersionFactory;
use App\Service\PackageVersionFactory;
use App\Service\SiteFactory;
use App\Types\DetectionType;
use App\Types\FrameworkTypes;
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
        private readonly ModuleVersionFactory $moduleVersionFactory,
        private readonly PackageVersionFactory $packageVersionFactory,
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

            $this->dockerImageTagFactory->setDockerImageTags($site->getInstallation(), $data->containers);

            foreach ($data->containers as $container) {
                if (isset($container->packages) && is_object($container->packages)) {
                    $this->packageVersionFactory->setPackageVersions($site->getInstallation(), $container->packages->installed);
                }
                if (isset($container->drupal) && is_object($container->drupal)) {
                    $this->moduleVersionFactory->setModuleVersions($site->getInstallation(), $container->drupal);
                    $this->setDrupal($site->getInstallation(), $container->drupal);
                }
                if (isset($container->symfony) && is_object($container->symfony)) {
                    $this->setSymfony($site->getInstallation(), $container->symfony);
                }
            }
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

    private function setSymfony(Installation $installation, object $symfony): void
    {
        if (empty($symfony->version)) {
            return;
        }

        $installation->setFrameworkVersion($symfony->version);

        if (isset($symfony->eof)) {
            $installation->setEol($symfony->eof);
        }
        if (isset($symfony->lts)) {
            $lts = 'Yes' === $symfony->lts;
            $installation->setLts($lts);
        }
        if (isset($symfony->phpVersion)) {
            $installation->setPhpVersion($symfony->phpVersion);
        }
    }

    private function setDrupal(Installation $installation, object $modules): void
    {
        foreach ($modules as $module) {
            if ('Core' === $module->package) {
                $installation->setFrameworkVersion($module->version);
                break;
            }
        }
        $installation->setType(FrameworkTypes::DRUPAL);
    }
}
