<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519181242 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE package (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, vendor VARCHAR(255) NOT NULL, package VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, homepage VARCHAR(255) DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, license VARCHAR(25) DEFAULT NULL, UNIQUE INDEX vendor_package (vendor, package), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package_version (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', package_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, version VARCHAR(25) NOT NULL, latest VARCHAR(25) NOT NULL, latest_status VARCHAR(50) NOT NULL, INDEX IDX_3047B64FF44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package_version_installation (package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_D04F3ED647A0D2F0 (package_version_id), INDEX IDX_D04F3ED6167B88B4 (installation_id), PRIMARY KEY(package_version_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE package_version ADD CONSTRAINT FK_3047B64FF44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE package_version_installation ADD CONSTRAINT FK_D04F3ED647A0D2F0 FOREIGN KEY (package_version_id) REFERENCES package_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_version_installation ADD CONSTRAINT FK_D04F3ED6167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE package_version DROP FOREIGN KEY FK_3047B64FF44CABFF');
        $this->addSql('ALTER TABLE package_version_installation DROP FOREIGN KEY FK_D04F3ED647A0D2F0');
        $this->addSql('DROP TABLE package');
        $this->addSql('DROP TABLE package_version');
        $this->addSql('DROP TABLE package_version_installation');
    }
}
