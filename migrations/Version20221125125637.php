<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221125125637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE git_repo (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, provider VARCHAR(255) NOT NULL, organization VARCHAR(255) NOT NULL, repo VARCHAR(255) NOT NULL, UNIQUE INDEX provider_org_repo (provider, organization, repo), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE git_tag (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', repo_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, tag VARCHAR(255) NOT NULL, INDEX IDX_9F72C4EDBD359B2D (repo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE git_tag ADD CONSTRAINT FK_9F72C4EDBD359B2D FOREIGN KEY (repo_id) REFERENCES git_repo (id)');
        $this->addSql('ALTER TABLE installation ADD git_tag_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\', ADD git_changes LONGTEXT NOT NULL, ADD git_changes_count INT NOT NULL, ADD git_cloned_scheme VARCHAR(10) NOT NULL');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB1D6E68406 FOREIGN KEY (git_tag_id) REFERENCES git_tag (id)');
        $this->addSql('CREATE INDEX IDX_1CBA6AB1D6E68406 ON installation (git_tag_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE installation DROP FOREIGN KEY FK_1CBA6AB1D6E68406');
        $this->addSql('ALTER TABLE git_tag DROP FOREIGN KEY FK_9F72C4EDBD359B2D');
        $this->addSql('DROP TABLE git_repo');
        $this->addSql('DROP TABLE git_tag');
        $this->addSql('DROP INDEX IDX_1CBA6AB1D6E68406 ON installation');
        $this->addSql('ALTER TABLE installation DROP git_tag_id, DROP git_changes, DROP git_changes_count, DROP git_cloned_scheme');
    }
}
