<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261021140107 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store advisories marked as not affected per git repo';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE advisory_assessment (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, repo_provider VARCHAR(255) NOT NULL, repo_organization VARCHAR(255) NOT NULL, repo_name VARCHAR(255) NOT NULL, advisory_id VARCHAR(255) NOT NULL, reason LONGTEXT NOT NULL, assessed_by VARCHAR(180) NOT NULL, UNIQUE INDEX repo_advisory (repo_provider, repo_organization, repo_name, advisory_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE advisory_assessment');
    }
}
