<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250116213800 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE advisory CHANGE id id BINARY(16) NOT NULL, CHANGE package_id package_id BINARY(16) NOT NULL, CHANGE reported_at reported_at DATETIME NOT NULL, CHANGE sources sources JSON NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE detection_result CHANGE id id BINARY(16) NOT NULL, CHANGE server_id server_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL, CHANGE last_contact last_contact DATETIME NOT NULL');
        $this->addSql('ALTER TABLE docker_image CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE docker_image_tag CHANGE id id BINARY(16) NOT NULL, CHANGE docker_image_id docker_image_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE domain CHANGE id id BINARY(16) NOT NULL, CHANGE server_id server_id BINARY(16) NOT NULL, CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL, CHANGE site_id site_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE git_repo CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE git_tag CHANGE id id BINARY(16) NOT NULL, CHANGE repo_id repo_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE installation CHANGE id id BINARY(16) NOT NULL, CHANGE server_id server_id BINARY(16) NOT NULL, CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL, CHANGE git_tag_id git_tag_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE installation_package_version CHANGE installation_id installation_id BINARY(16) NOT NULL, CHANGE package_version_id package_version_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE installation_module_version CHANGE installation_id installation_id BINARY(16) NOT NULL, CHANGE module_version_id module_version_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE installation_docker_image_tag CHANGE installation_id installation_id BINARY(16) NOT NULL, CHANGE docker_image_tag_id docker_image_tag_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE module CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE module_version CHANGE id id BINARY(16) NOT NULL, CHANGE module_id module_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE oidc CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE package CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE package_version CHANGE id id BINARY(16) NOT NULL, CHANGE package_id package_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE package_version_advisory CHANGE package_version_id package_version_id BINARY(16) NOT NULL, CHANGE advisory_id advisory_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE server CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE service_certificate CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE service_certificate_service CHANGE id id BINARY(16) NOT NULL, CHANGE certificate_id certificate_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL');
        $this->addSql('ALTER TABLE site CHANGE id id BINARY(16) NOT NULL, CHANGE server_id server_id BINARY(16) NOT NULL, CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL, CHANGE installation_id installation_id BINARY(16) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE id id BINARY(16) NOT NULL, CHANGE created_at created_at DATETIME NOT NULL, CHANGE modified_at modified_at DATETIME NOT NULL, CHANGE roles roles JSON NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE site CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE server_id server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE installation_id installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE module_version CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE module_id module_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE service_certificate CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE user CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE package_version CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE package_id package_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE package CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE docker_image_tag CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE docker_image_id docker_image_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE installation_module_version CHANGE installation_id installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE module_version_id module_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE server CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE domain CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE server_id server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE site_id site_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE docker_image CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE service_certificate_service CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE certificate_id certificate_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE package_version_advisory CHANGE package_version_id package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE advisory_id advisory_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE installation_docker_image_tag CHANGE installation_id installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE docker_image_tag_id docker_image_tag_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE installation_package_version CHANGE installation_id installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE package_version_id package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE git_repo CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE advisory CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE reported_at reported_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE sources sources JSON NOT NULL COMMENT \'(DC2Type:json)\', CHANGE package_id package_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE module CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE oidc CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE installation CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE server_id server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE detection_result_id detection_result_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE git_tag_id git_tag_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE detection_result CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE last_contact last_contact DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE server_id server_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE git_tag CHANGE id id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', CHANGE created_at created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE modified_at modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE repo_id repo_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
    }
}
