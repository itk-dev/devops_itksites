<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221124083804 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE git_git_remote DROP FOREIGN KEY FK_A8CFEF224D4CA094');
        $this->addSql('ALTER TABLE git_git_remote DROP FOREIGN KEY FK_A8CFEF223DCB893');
        $this->addSql('ALTER TABLE git DROP FOREIGN KEY FK_518E617C1844E6B7');
        $this->addSql('ALTER TABLE git DROP FOREIGN KEY FK_518E617CD74ABC36');
        $this->addSql('DROP TABLE git_git_remote');
        $this->addSql('DROP TABLE git');
        $this->addSql('DROP TABLE git_remote');
        $this->addSql('ALTER TABLE package_version CHANGE latest latest VARCHAR(25) DEFAULT NULL, CHANGE latest_status latest_status VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE git_git_remote (git_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', git_remote_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_A8CFEF224D4CA094 (git_id), INDEX IDX_A8CFEF223DCB893 (git_remote_id), PRIMARY KEY(git_id, git_remote_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE git (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, modified_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, root_dir VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, tag VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, changes LONGTEXT CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, changes_count INT NOT NULL, INDEX IDX_518E617CD74ABC36 (detection_result_id), UNIQUE INDEX server_rootDir (server_id, root_dir), INDEX IDX_518E617C1844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE git_remote (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, modified_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, url VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, UNIQUE INDEX UNIQ_368298A5F47645AE (url), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE git_git_remote ADD CONSTRAINT FK_A8CFEF224D4CA094 FOREIGN KEY (git_id) REFERENCES git (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE git_git_remote ADD CONSTRAINT FK_A8CFEF223DCB893 FOREIGN KEY (git_remote_id) REFERENCES git_remote (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE git ADD CONSTRAINT FK_518E617C1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE git ADD CONSTRAINT FK_518E617CD74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE package_version CHANGE latest latest VARCHAR(255) DEFAULT NULL, CHANGE latest_status latest_status VARCHAR(255) DEFAULT NULL');
    }
}
