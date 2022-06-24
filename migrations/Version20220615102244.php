<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220615102244 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE git (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, root_dir VARCHAR(255) NOT NULL, remote VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, changes LONGTEXT NOT NULL, INDEX IDX_518E617C1844E6B7 (server_id), INDEX IDX_518E617CD74ABC36 (detection_result_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE git ADD CONSTRAINT FK_518E617C1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE git ADD CONSTRAINT FK_518E617CD74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE git');
    }
}
