<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260519061123 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE security_contract DROP FOREIGN KEY `FK_8AE4AF8B166D1F9C`');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP INDEX UNIQ_8AE4AF8B166D1F9C ON security_contract');
        $this->addSql('ALTER TABLE security_contract ADD economics_id INT NOT NULL, ADD project_name VARCHAR(255) NOT NULL, ADD hosting_provider VARCHAR(255) DEFAULT NULL, ADD document_url VARCHAR(255) DEFAULT NULL, ADD eol TINYINT NOT NULL, ADD leantime_url VARCHAR(255) DEFAULT NULL, ADD client_contact_name VARCHAR(255) DEFAULT NULL, ADD client_contact_email VARCHAR(255) DEFAULT NULL, ADD dedicated_server TINYINT NOT NULL, ADD server_size VARCHAR(255) DEFAULT NULL, ADD system_owner_notices JSON DEFAULT NULL, ADD project_tracker_key VARCHAR(255) DEFAULT NULL, ADD cybersecurity_price DOUBLE PRECISION DEFAULT NULL, ADD cybersecurity_note LONGTEXT DEFAULT NULL, DROP notes, DROP project_id, CHANGE valid_from valid_from DATE DEFAULT NULL, CHANGE valid_to valid_to DATE DEFAULT NULL, CHANGE economics_report_url client_name VARCHAR(255) DEFAULT NULL, CHANGE operational_contract_url git_repos LONGTEXT DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8AE4AF8B4416F7E8 ON security_contract (economics_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE project (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, modified_by VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT \'\' NOT NULL COLLATE `utf8mb4_unicode_ci`, leantime_id INT NOT NULL, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, leantime_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, economics_url VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, details LONGTEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, leantime_modified_at DATETIME NOT NULL, UNIQUE INDEX UNIQ_2FB3D0EE4B785F0C (leantime_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('DROP INDEX UNIQ_8AE4AF8B4416F7E8 ON security_contract');
        $this->addSql('ALTER TABLE security_contract ADD economics_report_url VARCHAR(255) DEFAULT NULL, ADD operational_contract_url LONGTEXT DEFAULT NULL, ADD notes LONGTEXT NOT NULL, ADD project_id BINARY(16) NOT NULL, DROP economics_id, DROP project_name, DROP client_name, DROP hosting_provider, DROP document_url, DROP eol, DROP leantime_url, DROP client_contact_name, DROP client_contact_email, DROP dedicated_server, DROP server_size, DROP git_repos, DROP system_owner_notices, DROP project_tracker_key, DROP cybersecurity_price, DROP cybersecurity_note, CHANGE valid_from valid_from DATE NOT NULL, CHANGE valid_to valid_to DATE NOT NULL');
        $this->addSql('ALTER TABLE security_contract ADD CONSTRAINT `FK_8AE4AF8B166D1F9C` FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8AE4AF8B166D1F9C ON security_contract (project_id)');
    }
}
