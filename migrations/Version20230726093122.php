<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230726093122 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE advisory (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', package_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', advisory_id VARCHAR(255) NOT NULL, affected_versions VARCHAR(500) NOT NULL, title VARCHAR(255) NOT NULL, cve VARCHAR(50) DEFAULT NULL, link VARCHAR(255) NOT NULL, reported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', sources LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, UNIQUE INDEX UNIQ_4112BDD946CB6A73 (advisory_id), INDEX IDX_4112BDD9F44CABFF (package_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE package_version_advisory (package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', advisory_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_FBB6A71547A0D2F0 (package_version_id), INDEX IDX_FBB6A71546CB6A73 (advisory_id), PRIMARY KEY(package_version_id, advisory_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE advisory ADD CONSTRAINT FK_4112BDD9F44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE package_version_advisory ADD CONSTRAINT FK_FBB6A71547A0D2F0 FOREIGN KEY (package_version_id) REFERENCES package_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_version_advisory ADD CONSTRAINT FK_FBB6A71546CB6A73 FOREIGN KEY (advisory_id) REFERENCES advisory (id) ON DELETE CASCADE');
        $this->addSql('DROP INDEX vendor_package ON package');
        $this->addSql('ALTER TABLE package ADD advisory_count INT NOT NULL, CHANGE package name VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX vendor_name ON package (vendor, name)');
        $this->addSql('ALTER TABLE package_version ADD advisory_count INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory DROP FOREIGN KEY FK_4112BDD9F44CABFF');
        $this->addSql('ALTER TABLE package_version_advisory DROP FOREIGN KEY FK_FBB6A71547A0D2F0');
        $this->addSql('ALTER TABLE package_version_advisory DROP FOREIGN KEY FK_FBB6A71546CB6A73');
        $this->addSql('DROP TABLE advisory');
        $this->addSql('DROP TABLE package_version_advisory');
        $this->addSql('ALTER TABLE package_version DROP advisory_count');
        $this->addSql('DROP INDEX vendor_name ON package');
        $this->addSql('ALTER TABLE package DROP advisory_count, CHANGE name package VARCHAR(255) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX vendor_package ON package (vendor, package)');
    }
}
