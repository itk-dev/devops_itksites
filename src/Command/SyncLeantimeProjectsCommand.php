<?php

namespace App\Command;

use App\Service\Leantime\ProjectSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:leantime:sync-projects',
    description: 'Sync projects from Leantime',
)]
class SyncLeantimeProjectsCommand extends Command
{
    public function __construct(
        private readonly ProjectSyncService $projectSyncService,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('project-id', null, InputOption::VALUE_REQUIRED, 'Leantime project ID');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            if ($input->getOption('project-id')) {
                $projectId = (int) $input->getOption('project-id');

                $project = $this->projectSyncService->syncProject($projectId);

                $io->success(sprintf('Project: %s (%d) synced successfully.', $project->getName(), $project->getLeantimeId()));
            } else {
                $count = $this->projectSyncService->syncAllProjects();

                $io->success(sprintf('Synced %d projects successfully.', $count));
            }
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
