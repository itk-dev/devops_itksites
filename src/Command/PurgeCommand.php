<?php

declare(strict_types=1);

namespace App\Command;

use App\Repository\DetectionResultRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Scheduler\Attribute\AsPeriodicTask;

#[AsCommand(
    name: 'app:data:purge',
    description: 'Purge old data',
)]
#[AsPeriodicTask(frequency: '1 day', from: '03:00', jitter: 1800, arguments: '--date="30 days ago"')]
class PurgeCommand extends Command
{
    public function __construct(
        private readonly DetectionResultRepository $detectionResultRepository,
    ) {
        parent::__construct();
    }

    /**
     * Define the command.
     */
    protected function configure(): void
    {
        $this->addOption('date', null, InputOption::VALUE_REQUIRED, 'Remove results not seen since this date, e.g. "2026-09-21" or "30 days ago"');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $date = (string) $input->getOption('date');
        try {
            $dateTime = new \DateTime($date);
        } catch (\DateMalformedStringException) {
            $output->writeln('<error>Unknown date format:</error> '.$date);

            return Command::FAILURE;
        }
        $rows = $this->detectionResultRepository->remove($dateTime);

        $io->success('Removed '.$rows.' detection results from the database.');

        return Command::SUCCESS;
    }
}
