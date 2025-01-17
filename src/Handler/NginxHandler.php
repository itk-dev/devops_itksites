<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Service\DomainFactory;
use App\Service\SiteFactory;
use App\Types\DetectionType;
use App\Types\SiteType;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler for DetectionResult off type "nginx" (Sites, Domains).
 */
readonly class NginxHandler implements DetectionResultHandlerInterface
{
    private const string NGINX_DEFAULT = 'default';

    /**
     * DirectoryHandler constructor.
     *
     * @param SiteFactory $siteFactory
     * @param DomainFactory $domainFactory
     * @param ValidatorInterface $validator
     */
    public function __construct(
        private SiteFactory $siteFactory,
        private DomainFactory $domainFactory,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * {@inheritDoc}
     */
    public function handleResult(DetectionResult $detectionResult): void
    {
        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            // Nginx 'default' sites should not be indexed.
            if (str_ends_with($data->config, self::NGINX_DEFAULT)) {
                return;
            }

            $site = $this->siteFactory->getSite($data->config, $detectionResult);
            $site->setDetectionResult($detectionResult);
            $site->setType(SiteType::NGINX);
            $site->setPhpVersion($data->phpVersion);
            $site->setConfigFilePath($data->config);

            $domains = $this->domainFactory->getDomains($data->domain, $site, $detectionResult);
            $site->setDomains($domains);

            $errors = $this->validator->validate($site);

            if (count($errors) > 0) {
                // @TODO log validation error
            }
        } catch (\JsonException) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::NGINX === $type;
    }
}
