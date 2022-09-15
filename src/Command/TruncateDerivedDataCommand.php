<?php

namespace App\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:truncate:derived-data',
    description: 'Truncate all tables that hold derived data',
)]
class TruncateDerivedDataCommand extends Command
{
    // Following tables and join tables only contain data that can be
    // fully recreated from detection results.
    private const DERIVED_TABLES = [
        'docker_image',
        'docker_image_tag',
        'docker_image_tag_installation',
        'domain',
        'installation',
        'module',
        'module_version',
        'module_version_installation',
        'package',
        'package_version',
        'package_version_installation',
        'site',
    ];

    public function __construct(
        private readonly ManagerRegistry $managerRegistry
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            /** @var Connection $connection */
            $connection = $this->managerRegistry->getConnection();
            $dbPlatform = $connection->getDatabasePlatform();
            $connection->prepare('SET FOREIGN_KEY_CHECKS=0')->executeQuery();

            foreach (self::DERIVED_TABLES as $TABLE) {
                $sql = $dbPlatform->getTruncateTableSQL($TABLE);
                $connection->prepare($sql)->executeQuery();
            }

            $connection->prepare('SET FOREIGN_KEY_CHECKS=1')->executeQuery();
        } catch (Exception $e) {
            $io->error($e->getMessage());
        }

        $io->success('Derived tables/data truncated');

        return Command::SUCCESS;
    }
}
