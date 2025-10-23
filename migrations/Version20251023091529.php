<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251023091529 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE project (
              id BINARY(16) NOT NULL,
              created_at DATETIME NOT NULL,
              modified_at DATETIME NOT NULL,
              created_by VARCHAR(255) DEFAULT '' NOT NULL,
              modified_by VARCHAR(255) DEFAULT '' NOT NULL,
              leantime_id INT NOT NULL,
              name VARCHAR(255) NOT NULL,
              leantime_url VARCHAR(255) DEFAULT NULL,
              economics_url VARCHAR(255) DEFAULT NULL,
              details LONGTEXT DEFAULT NULL,
              leantime_modified_at DATETIME NOT NULL,
              UNIQUE INDEX UNIQ_2FB3D0EE4B785F0C (leantime_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE security_contract (
              id BINARY(16) NOT NULL,
              created_at DATETIME NOT NULL,
              modified_at DATETIME NOT NULL,
              created_by VARCHAR(255) DEFAULT '' NOT NULL,
              modified_by VARCHAR(255) DEFAULT '' NOT NULL,
              economics_report_url VARCHAR(255) DEFAULT NULL,
              operational_contract_url LONGTEXT DEFAULT NULL,
              monthly_price DOUBLE PRECISION DEFAULT NULL,
              quarterly_hours DOUBLE PRECISION DEFAULT NULL,
              valid_from DATE NOT NULL,
              valid_to DATE NOT NULL,
              active TINYINT(1) NOT NULL,
              notes LONGTEXT NOT NULL,
              project_id BINARY(16) NOT NULL,
              UNIQUE INDEX UNIQ_8AE4AF8B166D1F9C (project_id),
              PRIMARY KEY (id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              security_contract
            ADD
              CONSTRAINT FK_8AE4AF8B166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE security_contract DROP FOREIGN KEY FK_8AE4AF8B166D1F9C');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE security_contract');
    }
}
