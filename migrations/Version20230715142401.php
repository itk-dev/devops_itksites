<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230715142401 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE advisory (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', package_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', advisory_id VARCHAR(255) NOT NULL, affected_versions VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, cve VARCHAR(50) NOT NULL, link VARCHAR(255) NOT NULL, reported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sources LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, INDEX IDX_4112BDD9F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory ADD CONSTRAINT FK_4112BDD9F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('DROP INDEX vendor_package ON package');
        $this->addSql('ALTER TABLE package CHANGE package name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX vendor_name ON package (vendor, name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory DROP FOREIGN KEY FK_4112BDD9F44CABFF');
        $this->addSql('DROP TABLE advisory');
        $this->addSql('DROP INDEX vendor_name ON package');
        $this->addSql('ALTER TABLE package CHANGE name package VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX vendor_package ON package (vendor, package)');
    }
}
