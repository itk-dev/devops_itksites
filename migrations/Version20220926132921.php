<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220926132921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_version CHANGE version version VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE package_version CHANGE version version VARCHAR(255) NOT NULL, CHANGE latest latest VARCHAR(255) DEFAULT NULL, CHANGE latest_status latest_status VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_version CHANGE version version VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE package_version CHANGE version version VARCHAR(25) NOT NULL, CHANGE latest latest VARCHAR(25) DEFAULT NULL, CHANGE latest_status latest_status VARCHAR(50) DEFAULT NULL');
    }
}
