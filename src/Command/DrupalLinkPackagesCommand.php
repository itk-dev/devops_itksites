<?php

declare(strict_types=1);

namespace App\Command;

use App\Service\DrupalPackageLinker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'itksites:drupal:link-packages',
    description: 'Link Drupal modules and module versions to their Composer packages',
)]
readonly class DrupalLinkPackagesCommand
{
    public function __construct(
        private DrupalPackageLinker $drupalPackageLinker,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        [$modules, $moduleVersions] = $this->drupalPackageLinker->linkAll();

        $this->entityManager->flush();

        $io->success(sprintf('Linked %d modules and %d module versions.', $modules, $moduleVersions));

        return Command::SUCCESS;
    }
}
