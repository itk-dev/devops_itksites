<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261104205458 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the harvester version, errors and warnings of detection results';
    }

    public function up(Schema $schema): void
    {
        // Existing rows get empty lists before the columns become NOT NULL.
        $this->addSql('ALTER TABLE detection_result ADD harvester_version VARCHAR(255) DEFAULT NULL, ADD errors JSON DEFAULT NULL, ADD warnings JSON DEFAULT NULL');
        $this->addSql("UPDATE detection_result SET errors = '[]', warnings = '[]'");
        $this->addSql('ALTER TABLE detection_result MODIFY errors JSON NOT NULL, MODIFY warnings JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE detection_result DROP harvester_version, DROP errors, DROP warnings');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
