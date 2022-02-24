<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220224113244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE detection_result (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, type VARCHAR(255) NOT NULL, root_dir VARCHAR(255) NOT NULL, data LONGTEXT NOT NULL, INDEX IDX_9D26910F1844E6B7 (server_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE server (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, api_key VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, hosting_provider_name VARCHAR(255) DEFAULT NULL, internal_ip VARCHAR(15) DEFAULT NULL, external_ip VARCHAR(15) DEFAULT NULL, aarhus_ssl TINYINT(1) NOT NULL, lets_encrypt_ssl TINYINT(1) NOT NULL, veeam TINYINT(1) NOT NULL, azure_backup TINYINT(1) NOT NULL, monitoring TINYINT(1) NOT NULL, database_version VARCHAR(5) DEFAULT NULL, system VARCHAR(15) NOT NULL, note LONGTEXT DEFAULT NULL, service_desk_ticket VARCHAR(255) DEFAULT NULL, used_for VARCHAR(255) DEFAULT NULL, hosting_provider VARCHAR(25) NOT NULL, UNIQUE INDEX UNIQ_5A6DD5F6C912ED9D (api_key), UNIQUE INDEX UNIQ_5A6DD5F65E237E06 (name), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE detection_result ADD CONSTRAINT FK_9D26910F1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detection_result DROP FOREIGN KEY FK_9D26910F1844E6B7');
        $this->addSql('DROP TABLE detection_result');
        $this->addSql('DROP TABLE server');
    }
}
