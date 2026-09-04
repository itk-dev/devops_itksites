<?php

namespace App\Command;

use App\Service\ServiceAgreementSyncService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:economics:sync-agreements',
    description: 'Sync service agreements from Economics',
)]
class SyncServiceAgreementsCommand extends Command
{
    public function __construct(
        private readonly ServiceAgreementSyncService $syncService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $result = $this->syncService->syncAll();

            $io->success(sprintf('Synced %d projects successfully.', $result['projects']));

            if (!empty($result['unmatchedRepoNames'])) {
                $io->warning(sprintf(
                    'Could not link %d GitHub repo name(s) to existing GitRepo entries: %s',
                    count($result['unmatchedRepoNames']),
                    implode(', ', $result['unmatchedRepoNames']),
                ));
            }
        } catch (\Throwable $e) {
            $io->error($e->getMessage());

            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
