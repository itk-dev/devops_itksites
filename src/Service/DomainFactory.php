<?php

namespace App\Service;

use App\Entity\DetectionResult;
use App\Entity\Domain;
use App\Entity\Site;
use App\Repository\DomainRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DomainFactory
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DomainRepository $domainRepository,
        private readonly ValidatorInterface $validator,
    ) {
    }

    /**
     * @param string $addressList
     * @param Site $site
     * @param DetectionResult $detectionResult
     *
     * @return Collection<int, Domain>
     */
    public function getDomains(string $addressList, Site $site, DetectionResult $detectionResult): Collection
    {
        $domains = new ArrayCollection();
        $domainStrings = explode(' ', $addressList);

        foreach ($domainStrings as $domainString) {
            if (empty($domainString)) {
                continue;
            }

            // Remove ' and " from beginning and end of domain string if present along with the default "blanks"
            $domainString = \trim($domainString, " \n\r\t\v\x00'\"");

            $domain = $this->getDomain($domainString, $site, $detectionResult);

            $errors = $this->validator->validate($domain);
            if (count($errors) > 0) {
                // @TODO log validation error
            } else {
                $domains->add($domain);
            }
        }

        return $domains;
    }

    public function getDomain(string $address, Site $site, DetectionResult $detectionResult): Domain
    {
        $domain = $this->domainRepository->findOneBy([
            'address' => $address,
            'site' => $site,
        ]);

        if (null === $domain) {
            $domain = new Domain();
            $this->entityManager->persist($domain);
        }

        $domain->setSite($site);
        $domain->setAddress($address);
        $domain->setDetectionResult($detectionResult);

        return $domain;
    }
}
