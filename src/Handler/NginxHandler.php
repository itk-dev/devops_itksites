<?php

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Domain;
use App\Entity\Site;
use App\Types\DetectionType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Handler for DetectionResult off type "nginx".
 */
class NginxHandler implements DetectionResultHandlerInterface
{
    private const NGINX_DEFAULT = 'default';

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
        $siteRepository = $this->entityManager->getRepository(Site::class);
        $domainRepository = $this->entityManager->getRepository(Domain::class);

        try {
            $data = \json_decode($detectionResult->getData(), false, 512, JSON_THROW_ON_ERROR);

            // Nginx 'default' sites should not be indexed.
            if (str_ends_with($data->config, self::NGINX_DEFAULT)) {
                return;
            }

            $site = $siteRepository->findOneBy([
                'configFilePath' => $data->config,
                'server' => $detectionResult->getServer(),
            ]);

            if (null === $site) {
                $site = new Site();
            }

            $domainStrings = explode(' ', $data->domain);
            foreach ($domainStrings as $domainString) {
                $domain = $domainRepository->findOneBy([
                    'address' => $domainString,
                    'site' => $site,
                ]);

                if (null === $domain) {
                    $domain = new Domain();
                }

                $domain->setDetectionResult($detectionResult);
                $domain->setAddress($domainString);
                $domain->setSite($site);

                $errors = $this->validator->validate($domain);
                if (count($errors) > 0) {
                    // @TODO log validation error
                } else {
                    $site->addDomain($domain);
                }
            }

            $site->setPhpVersion($data->phpVersion);
            $site->setConfigFilePath($data->config);
            $site->setDetectionResult($detectionResult);

            $errors = $this->validator->validate($site);

            if (count($errors) > 0) {
                // @TODO log validation error
            } else {
                $this->entityManager->persist($site);
                $this->entityManager->flush();
            }
        } catch (\JsonException $e) {
            // @TODO log exceptions
        }
    }

    /** {@inheritDoc} */
    public function supportsType(string $type): bool
    {
        return DetectionType::NGINX === $type;
    }
}
