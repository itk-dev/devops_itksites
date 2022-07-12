<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220712081714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE docker_image (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, organization VARCHAR(255) NOT NULL, repository VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, UNIQUE INDEX organization_repository (organization, repository), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE docker_image_tag (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', docker_image_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, name VARCHAR(50) NOT NULL, tag VARCHAR(50) NOT NULL, INDEX IDX_ED319DB59727D52 (docker_image_id), UNIQUE INDEX dockerImage_name_tag (docker_image_id, name, tag), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE docker_image_tag_installation (docker_image_tag_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_2555E2DC30E50D4B (docker_image_tag_id), INDEX IDX_2555E2DC167B88B4 (installation_id), PRIMARY KEY(docker_image_tag_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE docker_image_tag ADD CONSTRAINT FK_ED319DB59727D52 FOREIGN KEY (docker_image_id) REFERENCES docker_image (id)');
        $this->addSql('ALTER TABLE docker_image_tag_installation ADD CONSTRAINT FK_2555E2DC30E50D4B FOREIGN KEY (docker_image_tag_id) REFERENCES docker_image_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE docker_image_tag_installation ADD CONSTRAINT FK_2555E2DC167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX type_idx ON detection_result (type)');
        $this->addSql('CREATE UNIQUE INDEX module_version ON module_version (module_id, version)');
        $this->addSql('CREATE UNIQUE INDEX package_version ON package_version (package_id, version)');
        $this->addSql('DROP INDEX server_configfilepath_idx ON site');
        $this->addSql('ALTER TABLE site ADD type VARCHAR(25) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX server_rootDir_configFilePath_idx ON site (server_id, root_dir, config_file_path)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE docker_image_tag DROP FOREIGN KEY FK_ED319DB59727D52');
        $this->addSql('ALTER TABLE docker_image_tag_installation DROP FOREIGN KEY FK_2555E2DC30E50D4B');
        $this->addSql('DROP TABLE docker_image');
        $this->addSql('DROP TABLE docker_image_tag');
        $this->addSql('DROP TABLE docker_image_tag_installation');
        $this->addSql('DROP INDEX type_idx ON detection_result');
        $this->addSql('DROP INDEX module_version ON module_version');
        $this->addSql('DROP INDEX package_version ON package_version');
        $this->addSql('DROP INDEX server_rootDir_configFilePath_idx ON site');
        $this->addSql('ALTER TABLE site DROP type');
        $this->addSql('CREATE UNIQUE INDEX server_configfilepath_idx ON site (server_id, config_file_path)');
    }
}
