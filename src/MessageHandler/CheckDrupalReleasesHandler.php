<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CheckDrupalPackageRelease;
use App\Message\CheckDrupalReleases;
use App\Repository\PackageRepository;
use App\Service\DrupalPackageLinker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * Reconciles module/package links, then fans out one check per drupal/* package
 * so failures are isolated and retried per package.
 */
#[AsMessageHandler]
final readonly class CheckDrupalReleasesHandler
{
    private const string VENDOR = 'drupal';

    public function __construct(
        private DrupalPackageLinker $drupalPackageLinker,
        private PackageRepository $packageRepository,
        private EntityManagerInterface $entityManager,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CheckDrupalReleases $message): void
    {
        $this->drupalPackageLinker->linkAll();
        $this->entityManager->flush();

        foreach ($this->packageRepository->findBy(['vendor' => self::VENDOR]) as $package) {
            $this->messageBus->dispatch(new CheckDrupalPackageRelease($package->getId()));
        }
    }
}
