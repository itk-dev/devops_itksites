<?php

declare(strict_types=1);

namespace App\Service\Drupal;

use App\Entity\Advisory;
use App\Entity\Package;
use App\Repository\AdvisoryRepository;
use App\Service\AdvisoryFactory;
use App\Utils\DrupalVersion;
use Composer\Semver\Semver;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Records drupal.org release status on a drupal/* package's versions and
 * turns the security advisories behind insecure versions into Advisories.
 */
class ReleaseChecker
{
    private const string VENDOR = 'drupal';
    private const string SOURCE_NAME = 'Drupal';

    public function __construct(
        private readonly ReleaseHistoryClient $releaseHistoryClient,
        private readonly SecurityAdvisoryClient $securityAdvisoryClient,
        private readonly AdvisoryFactory $advisoryFactory,
        private readonly AdvisoryRepository $advisoryRepository,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function check(Package $package): void
    {
        $project = (string) $package->getName();
        if (self::VENDOR !== $package->getVendor()) {
            return;
        }

        $history = $this->releaseHistoryClient->fetch($project);
        $checkedAt = new \DateTimeImmutable();
        $insecureVersions = [];
        foreach ($package->getPackageVersions() as $packageVersion) {
            $release = $history?->get((string) $packageVersion->getVersion());
            $packageVersion
                ->setDrupalReleaseTerms($release?->terms)
                ->setDrupalInsecure($release->insecure ?? false)
                ->setDrupalReleaseCheckedAt($checkedAt);
            if ($packageVersion->isDrupalInsecure()) {
                $insecureVersions[] = (string) $packageVersion->getVersion();
            }
        }

        // An insecure version no advisory matches stays flagged without one.
        if ([] !== $insecureVersions) {
            foreach ($this->securityAdvisoryClient->fetch($project) as $securityAdvisory) {
                $constraint = DrupalVersion::constraintToComposer($securityAdvisory->affectedVersions);
                if ($this->matchesAny($insecureVersions, $constraint)) {
                    $advisory = $this->upsert($package, $securityAdvisory, $constraint);
                    $this->advisoryFactory->setAdvisoryForAffectedVersions($package, $advisory);
                }
            }
        }

        $this->entityManager->flush();
    }

    /**
     * @param list<string> $versions
     */
    private function matchesAny(array $versions, string $constraint): bool
    {
        foreach ($versions as $version) {
            try {
                if (Semver::satisfies($version, $constraint)) {
                    return true;
                }
            } catch (\UnexpectedValueException) {
                return false;
            }
        }

        return false;
    }

    private function upsert(Package $package, SecurityAdvisory $securityAdvisory, string $constraint): Advisory
    {
        $advisory = $this->advisoryRepository->findOneBy(['advisoryId' => $securityAdvisory->advisoryId]);
        if (null === $advisory) {
            $advisory = new Advisory()->setAdvisoryId($securityAdvisory->advisoryId);
            $this->entityManager->persist($advisory);
            $package->addAdvisory($advisory);
        }

        $advisory
            ->setAffectedVersions($constraint)
            ->setTitle($securityAdvisory->title)
            ->setLink($securityAdvisory->url)
            ->setReportedAt($securityAdvisory->created)
            ->setSources([['name' => self::SOURCE_NAME, 'remoteId' => $securityAdvisory->advisoryId]]);
        if (null !== $securityAdvisory->cve) {
            $advisory->setCve($securityAdvisory->cve);
        }

        return $advisory;
    }
}
