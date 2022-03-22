<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220322124725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE installation (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, root_dir VARCHAR(255) NOT NULL, INDEX IDX_1CBA6AB11844E6B7 (server_id), UNIQUE INDEX UNIQ_1CBA6AB1D74ABC36 (detection_result_id), UNIQUE INDEX server_rootdir_idx (server_id, root_dir), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB11844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB1D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE detection_result ADD last_contact DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('UPDATE detection_result SET last_contact = modified_at');
        $this->addSql('CREATE UNIQUE INDEX server_hash_idx ON detection_result (server_id, hash)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE installation');
        $this->addSql('DROP INDEX server_hash_idx ON detection_result');
        $this->addSql('ALTER TABLE detection_result DROP last_contact');
    }
}
