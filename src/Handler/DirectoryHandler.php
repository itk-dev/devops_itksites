<?php

declare(strict_types=1);

namespace App\Handler;

use App\Entity\DetectionResult;
use App\Entity\Installation;
use App\Repository\DetectionResultRepository;
use App\Repository\SiteRepository;
use App\Service\InstallationFactory;
use App\Types\DetectionType;
use App\Utils\RootDirNormalizer;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Handler for DetectionResult off type "dir" (Installations).
 */
readonly class DirectoryHandler implements DetectionResultHandlerInterface
{
    /**
     * DirectoryHandler constructor.
     */
    public function __construct(
        private SiteRepository $siteRepository,
        private InstallationFactory $installationFactory,
        private DetectionResultRepository $detectionResultRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function handleResult(DetectionResult $detectionResult): void
    {
        $server = $detectionResult->getServer();

        // Only the latest dir result describes the server. Older ones (e.g. from a replay) are history.
        $latest = $this->detectionResultRepository->findOneBy(['server' => $server, 'type' => DetectionType::DIRECTORY], ['lastContact' => 'DESC']);
        if ($latest !== $detectionResult) {
            return;
        }

        $installations = $this->installationFactory->getInstallations($detectionResult);

        // Sites belong to the listed dir they are in. Sites outside every listed dir are gone.
        foreach ($this->siteRepository->findBy(['server' => $server]) as $site) {
            $installation = $this->findEnclosing($installations, $site->getRootDir());
            if (null === $installation) {
                $this->entityManager->remove($site);
            } else {
                $installation->addSite($site);
            }
        }

        $server->setInstallations($installations);

        // Delete results from dirs no longer listed, so a replay cannot recreate them.
        foreach ($this->detectionResultRepository->findBy(['server' => $server]) as $result) {
            if (DetectionType::DIRECTORY !== $result->getType() && null === $this->findEnclosing($installations, $result->getRootDir())) {
                $this->entityManager->remove($result);
            }
        }
    }

    public function supportsType(string $type): bool
    {
        return DetectionType::DIRECTORY === $type;
    }

    /**
     * Find the installation for the root dir itself or, failing that, its closest parent dir.
     *
     * @param Collection<int, Installation> $installations
     */
    private function findEnclosing(Collection $installations, string $rootDir): ?Installation
    {
        $rootDir = RootDirNormalizer::normalize($rootDir);
        $enclosing = null;
        foreach ($installations as $installation) {
            $dir = $installation->getRootDir();
            if (($rootDir === $dir || str_starts_with($rootDir, $dir.'/'))
                && strlen($dir) > strlen($enclosing?->getRootDir() ?? '')) {
                $enclosing = $installation;
            }
        }

        return $enclosing;
    }
}
