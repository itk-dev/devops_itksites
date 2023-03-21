<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230122090342 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE service_certificate (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, domain VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, one_password_url VARCHAR(255) NOT NULL, expiration_time DATETIME NOT NULL, usage_documentation_url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service_certificate_service (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', certificate_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, type VARCHAR(255) NOT NULL, system_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', one_password_url VARCHAR(255) NOT NULL, INDEX IDX_75A9E79799223FFD (certificate_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE service_certificate_service ADD CONSTRAINT FK_75A9E79799223FFD FOREIGN KEY (certificate_id) REFERENCES service_certificate (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_certificate_service DROP FOREIGN KEY FK_75A9E79799223FFD');
        $this->addSql('DROP TABLE service_certificate');
        $this->addSql('DROP TABLE service_certificate_service');
    }
}
