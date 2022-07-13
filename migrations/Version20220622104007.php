<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220622104007 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX server_rootDir ON git (server_id, root_dir)');
        $this->addSql('CREATE TABLE git_git_remote (git_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', git_remote_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_A8CFEF224D4CA094 (git_id), INDEX IDX_A8CFEF223DCB893 (git_remote_id), PRIMARY KEY(git_id, git_remote_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE git_remote (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, url VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_368298A5F47645AE (url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE git_git_remote ADD CONSTRAINT FK_A8CFEF224D4CA094 FOREIGN KEY (git_id) REFERENCES git (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE git_git_remote ADD CONSTRAINT FK_A8CFEF223DCB893 FOREIGN KEY (git_remote_id) REFERENCES git_remote (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE git ADD changes_count INT NOT NULL, DROP remote');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE git_git_remote DROP FOREIGN KEY FK_A8CFEF223DCB893');
        $this->addSql('DROP TABLE git_git_remote');
        $this->addSql('DROP TABLE git_remote');
        $this->addSql('ALTER TABLE git ADD remote VARCHAR(255) NOT NULL, DROP changes_count');
        $this->addSql('DROP INDEX server_rootDir ON git');
    }
}
