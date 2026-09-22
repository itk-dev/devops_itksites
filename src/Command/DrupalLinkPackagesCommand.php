<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\ModuleVersionRepository;
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
        private ModuleVersionRepository $moduleVersionRepository,
        private DrupalPackageLinker $drupalPackageLinker,
        private EntityManagerInterface $entityManager,
    ) {
    }

    public function __invoke(SymfonyStyle $io): int
    {
        $modules = [];
        $moduleVersions = 0;
        $changed = 0;

        // Every link has a module version on one end, so walking them covers both sides.
        foreach ($this->moduleVersionRepository->findAll() as $moduleVersion) {
            if ($this->drupalPackageLinker->linkModuleVersion($moduleVersion)) {
                ++$changed;
            }

            $module = $moduleVersion->getModule();
            if (null !== $module->getComposerPackage()) {
                $modules[spl_object_id($module)] = true;
            }
            if (null !== $moduleVersion->getComposerPackageVersion()) {
                ++$moduleVersions;
            }
        }

        $this->entityManager->flush();

        $io->success(sprintf(
            'Linked %d modules and %d module versions (%d module versions changed).',
            count($modules),
            $moduleVersions,
            $changed,
        ));

        return Command::SUCCESS;
    }
}
