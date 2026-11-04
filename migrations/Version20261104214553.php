<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261104214553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store long advisory values as text, and why parts of a detection result could not be processed';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory CHANGE affected_versions affected_versions LONGTEXT NOT NULL, CHANGE title title LONGTEXT NOT NULL, CHANGE link link LONGTEXT NOT NULL');
        // Existing rows get an empty list before the column becomes NOT NULL.
        $this->addSql('ALTER TABLE detection_result ADD processing_errors JSON DEFAULT NULL');
        $this->addSql("UPDATE detection_result SET processing_errors = '[]'");
        $this->addSql('ALTER TABLE detection_result MODIFY processing_errors JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE advisory CHANGE affected_versions affected_versions VARCHAR(500) NOT NULL, CHANGE title title VARCHAR(255) NOT NULL, CHANGE link link VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE detection_result DROP processing_errors');
    }

    public function isTransactional(): bool
    {
        return false;
    }
}
