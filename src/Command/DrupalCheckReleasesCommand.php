<?php

declare(strict_types=1);

namespace App\Command;

use App\Message\CheckDrupalReleases;
use App\Repository\PackageRepository;
use App\Service\Drupal\ReleaseChecker;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Attribute\Option;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsCommand(
    name: 'app:drupal:check-releases',
    description: 'Check drupal.org release status and security advisories for drupal/* packages',
)]
readonly class DrupalCheckReleasesCommand
{
    public function __construct(
        private PackageRepository $packageRepository,
        private ReleaseChecker $releaseChecker,
        private MessageBusInterface $messageBus,
        #[Target('cache.drupal_org')]
        private CacheItemPoolInterface $cache,
    ) {
    }

    public function __invoke(
        SymfonyStyle $io,
        #[Option('Check one package synchronously, e.g. drupal/key_auth')]
        ?string $package = null,
        #[Option('Bypass the drupal.org cache')]
        bool $refresh = false,
    ): int {
        if ($refresh) {
            $this->cache->clear();
        }

        if (null === $package) {
            $this->messageBus->dispatch(new CheckDrupalReleases());
            $io->success('Queued a release check of all drupal/* packages.');

            return Command::SUCCESS;
        }

        [$vendor, $name] = explode('/', $package, 2) + [1 => ''];
        $entity = $this->packageRepository->findOneBy(['vendor' => $vendor, 'name' => $name]);
        if (null === $entity) {
            $io->error(sprintf('Package %s not found.', $package));

            return Command::FAILURE;
        }

        $this->releaseChecker->check($entity);

        $rows = [];
        foreach ($entity->getPackageVersions() as $packageVersion) {
            $rows[] = [
                $packageVersion->getVersion(),
                $packageVersion->isDrupalInsecure() ? 'insecure' : '',
                implode(', ', $packageVersion->getAdvisories()->map(static fn ($advisory) => $advisory->getAdvisoryId())->toArray()),
            ];
        }
        $io->table(['Version', 'Status', 'Advisories'], $rows);

        return Command::SUCCESS;
    }
}
