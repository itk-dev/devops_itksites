<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220519083929 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site ADD installation_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id)');
        $this->addSql('CREATE INDEX IDX_694309E4167B88B4 ON site (installation_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4167B88B4');
        $this->addSql('DROP INDEX IDX_694309E4167B88B4 ON site');
        $this->addSql('ALTER TABLE site DROP installation_id');
    }
}
