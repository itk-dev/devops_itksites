<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260923085236 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Record drupal.org release status on package versions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package_version ADD drupal_release_terms JSON DEFAULT NULL, ADD drupal_insecure TINYINT DEFAULT 0 NOT NULL, ADD drupal_release_checked_at DATETIME DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE package_version DROP drupal_release_terms, DROP drupal_insecure, DROP drupal_release_checked_at');
    }
}
