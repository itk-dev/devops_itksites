<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220321091734 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE detection_result ADD hash VARCHAR(255) NOT NULL');
        $this->addSql('UPDATE detection_result set hash = UUID()');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9D26910FD1B862B8 ON detection_result (hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_9D26910FD1B862B8 ON detection_result');
        $this->addSql('ALTER TABLE detection_result DROP hash');
    }
}
