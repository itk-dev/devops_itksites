<?php

declare(strict_types=1);

namespace App\MessageHandler;

use App\Message\CheckDrupalPackageRelease;
use App\Repository\PackageRepository;
use App\Service\Drupal\ReleaseChecker;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class CheckDrupalPackageReleaseHandler
{
    public function __construct(
        private PackageRepository $packageRepository,
        private ReleaseChecker $releaseChecker,
    ) {
    }

    public function __invoke(CheckDrupalPackageRelease $message): void
    {
        $package = $this->packageRepository->find($message->packageId);

        // The package may be gone by the time the message is handled.
        if (null !== $package) {
            $this->releaseChecker->check($package);
        }
    }
}
