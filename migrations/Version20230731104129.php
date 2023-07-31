<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230731104129 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oidc ADD expiration_time DATETIME NOT NULL');
        $this->addSql('UPDATE oidc SET expiration_time = expiration_date');
        $this->addSql('ALTER TABLE oidc DROP expiration_date');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE oidc ADD expiration_date DATE NOT NULL');
        $this->addSql('UPDATE oidc SET expiration_date = expiration_time');
        $this->addSql('DROP expiration_time');
    }
}
