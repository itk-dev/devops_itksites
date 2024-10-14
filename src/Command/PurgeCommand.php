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

#[AsCommand(
    name: 'app:data:purge',
    description: 'Purge old data',
)]
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
        $this->addOption('date', null, InputOption::VALUE_REQUIRED, 'Remove results older that this date (Y-m-d)');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $format = 'Y-m-d';
        $date = (string) $input->getOption('date');
        $dateTime = \DateTime::createFromFormat($format, $date);
        if (false === $dateTime || $dateTime->format($format) !== $date) {
            $output->writeln('<error>Unknown date format:</error> '.$date);

            return Command::FAILURE;
        }
        $rows = $this->detectionResultRepository->remove($dateTime);

        $io->success('Removed '.$rows.' detection results from the database.');

        return Command::SUCCESS;
    }
}
