<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260520101548 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE security_contract (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, economics_id INT NOT NULL, project_name VARCHAR(255) NOT NULL, client_name VARCHAR(255) DEFAULT NULL, hosting_provider VARCHAR(255) DEFAULT NULL, document_url VARCHAR(255) DEFAULT NULL, monthly_price DOUBLE PRECISION DEFAULT NULL, valid_from DATE DEFAULT NULL, valid_to DATE DEFAULT NULL, active TINYINT NOT NULL, eol TINYINT NOT NULL, leantime_url VARCHAR(255) DEFAULT NULL, client_contact_name VARCHAR(255) DEFAULT NULL, client_contact_email VARCHAR(255) DEFAULT NULL, dedicated_server TINYINT NOT NULL, server_size VARCHAR(255) DEFAULT NULL, git_repos LONGTEXT DEFAULT NULL, system_owner_notices JSON DEFAULT NULL, project_tracker_key VARCHAR(255) DEFAULT NULL, quarterly_hours DOUBLE PRECISION DEFAULT NULL, cybersecurity_price DOUBLE PRECISION DEFAULT NULL, cybersecurity_note LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_8AE4AF8B4416F7E8 (economics_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE security_contract');
    }
}
