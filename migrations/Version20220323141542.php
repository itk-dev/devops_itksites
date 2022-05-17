<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220323141542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE domain (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, root_dir VARCHAR(255) NOT NULL, address VARCHAR(255) NOT NULL, INDEX IDX_A7A91E0B1844E6B7 (server_id), INDEX IDX_A7A91E0BD74ABC36 (detection_result_id), INDEX IDX_A7A91E0BF6BD1646 (site_id), UNIQUE INDEX site_address_idx (site_id, address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE site (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, root_dir VARCHAR(255) NOT NULL, php_version VARCHAR(10) NOT NULL, config_file_path VARCHAR(255) NOT NULL, INDEX IDX_694309E41844E6B7 (server_id), INDEX IDX_694309E4D74ABC36 (detection_result_id), UNIQUE INDEX server_configfilepath_idx (server_id, config_file_path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BD74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E41844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE installation DROP INDEX UNIQ_1CBA6AB1D74ABC36, ADD INDEX IDX_1CBA6AB1D74ABC36 (detection_result_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0BF6BD1646');
        $this->addSql('DROP TABLE domain');
        $this->addSql('DROP TABLE site');
        $this->addSql('ALTER TABLE installation DROP INDEX IDX_1CBA6AB1D74ABC36, ADD UNIQUE INDEX UNIQ_1CBA6AB1D74ABC36 (detection_result_id)');
    }
}
