<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230801085216 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE oidc ADD domain VARCHAR(255) NOT NULL, ADD expiration_time DATETIME NOT NULL, ADD one_password_url VARCHAR(255) NOT NULL, ADD usage_documentation_url VARCHAR(255) NOT NULL, DROP site, DROP expiration_date, DROP claims, DROP ad, DROP discovery_url, DROP graph');
        $this->addSql('ALTER TABLE service_certificate_service DROP system_uuid');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service_certificate_service ADD system_uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE oidc ADD site VARCHAR(255) NOT NULL, ADD expiration_date DATE NOT NULL, ADD claims LONGTEXT NOT NULL, ADD ad LONGTEXT NOT NULL, ADD discovery_url VARCHAR(255) NOT NULL, ADD graph VARCHAR(255) DEFAULT NULL, DROP domain, DROP expiration_time, DROP one_password_url, DROP usage_documentation_url');
    }
}
