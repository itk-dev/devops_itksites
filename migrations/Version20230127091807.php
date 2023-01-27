<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230127091807 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE installation_package_version (installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_BF0F3E3C167B88B4 (installation_id), INDEX IDX_BF0F3E3C47A0D2F0 (package_version_id), PRIMARY KEY(installation_id, package_version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE installation_module_version (installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', module_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_39716106167B88B4 (installation_id), INDEX IDX_39716106DE590C98 (module_version_id), PRIMARY KEY(installation_id, module_version_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE installation_docker_image_tag (installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', docker_image_tag_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_24B2F5BB167B88B4 (installation_id), INDEX IDX_24B2F5BB30E50D4B (docker_image_tag_id), PRIMARY KEY(installation_id, docker_image_tag_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE installation_package_version ADD CONSTRAINT FK_BF0F3E3C167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_package_version ADD CONSTRAINT FK_BF0F3E3C47A0D2F0 FOREIGN KEY (package_version_id) REFERENCES package_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_module_version ADD CONSTRAINT FK_39716106167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_module_version ADD CONSTRAINT FK_39716106DE590C98 FOREIGN KEY (module_version_id) REFERENCES module_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_docker_image_tag ADD CONSTRAINT FK_24B2F5BB167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_docker_image_tag ADD CONSTRAINT FK_24B2F5BB30E50D4B FOREIGN KEY (docker_image_tag_id) REFERENCES docker_image_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_version_installation DROP FOREIGN KEY FK_54CEDC77167B88B4');
        $this->addSql('ALTER TABLE module_version_installation DROP FOREIGN KEY FK_54CEDC77DE590C98');
        $this->addSql('ALTER TABLE package_version_installation DROP FOREIGN KEY FK_D04F3ED6167B88B4');
        $this->addSql('ALTER TABLE package_version_installation DROP FOREIGN KEY FK_D04F3ED647A0D2F0');
        $this->addSql('ALTER TABLE docker_image_tag_installation DROP FOREIGN KEY FK_2555E2DC167B88B4');
        $this->addSql('ALTER TABLE docker_image_tag_installation DROP FOREIGN KEY FK_2555E2DC30E50D4B');
        $this->addSql('DROP TABLE module_version_installation');
        $this->addSql('DROP TABLE package_version_installation');
        $this->addSql('DROP TABLE docker_image_tag_installation');
        $this->addSql('ALTER TABLE detection_result DROP FOREIGN KEY FK_9D26910F1844E6B7');
        $this->addSql('ALTER TABLE detection_result ADD CONSTRAINT FK_9D26910F1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE docker_image_tag DROP FOREIGN KEY FK_ED319DB59727D52');
        $this->addSql('ALTER TABLE docker_image_tag ADD CONSTRAINT FK_ED319DB59727D52 FOREIGN KEY (docker_image_id) REFERENCES docker_image (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0BF6BD1646');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0B1844E6B7');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0BD74ABC36');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BD74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE git_tag DROP FOREIGN KEY FK_9F72C4EDBD359B2D');
        $this->addSql('ALTER TABLE git_tag ADD CONSTRAINT FK_9F72C4EDBD359B2D FOREIGN KEY (repo_id) REFERENCES git_repo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation DROP FOREIGN KEY FK_1CBA6AB1D74ABC36');
        $this->addSql('ALTER TABLE installation DROP FOREIGN KEY FK_1CBA6AB11844E6B7');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB1D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB11844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_version DROP FOREIGN KEY FK_1467EDDAAFC2B591');
        $this->addSql('ALTER TABLE module_version ADD CONSTRAINT FK_1467EDDAAFC2B591 FOREIGN KEY (module_id) REFERENCES module (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package ADD abandoned TINYINT(1) DEFAULT NULL, ADD warning VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE package_version DROP FOREIGN KEY FK_3047B64FF44CABFF');
        $this->addSql('ALTER TABLE package_version CHANGE latest latest VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE package_version ADD CONSTRAINT FK_3047B64FF44CABFF FOREIGN KEY (package_id) REFERENCES package (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E41844E6B7');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4D74ABC36');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4167B88B4');
        // Cleanup sites and domains with no installation
        $this->addSql('DELETE d FROM domain d LEFT JOIN site s ON d.site_id = s.id WHERE s.installation_id IS NULL');
        $this->addSql('DELETE s FROM site s WHERE s.installation_id IS NULL');
        $this->addSql('ALTER TABLE site CHANGE installation_id installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E41844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module_version_installation (module_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_54CEDC77DE590C98 (module_version_id), INDEX IDX_54CEDC77167B88B4 (installation_id), PRIMARY KEY(module_version_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE package_version_installation (package_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_D04F3ED647A0D2F0 (package_version_id), INDEX IDX_D04F3ED6167B88B4 (installation_id), PRIMARY KEY(package_version_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE docker_image_tag_installation (docker_image_tag_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_2555E2DC30E50D4B (docker_image_tag_id), INDEX IDX_2555E2DC167B88B4 (installation_id), PRIMARY KEY(docker_image_tag_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE module_version_installation ADD CONSTRAINT FK_54CEDC77167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_version_installation ADD CONSTRAINT FK_54CEDC77DE590C98 FOREIGN KEY (module_version_id) REFERENCES module_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_version_installation ADD CONSTRAINT FK_D04F3ED6167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE package_version_installation ADD CONSTRAINT FK_D04F3ED647A0D2F0 FOREIGN KEY (package_version_id) REFERENCES package_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE docker_image_tag_installation ADD CONSTRAINT FK_2555E2DC167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE docker_image_tag_installation ADD CONSTRAINT FK_2555E2DC30E50D4B FOREIGN KEY (docker_image_tag_id) REFERENCES docker_image_tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation_package_version DROP FOREIGN KEY FK_BF0F3E3C167B88B4');
        $this->addSql('ALTER TABLE installation_package_version DROP FOREIGN KEY FK_BF0F3E3C47A0D2F0');
        $this->addSql('ALTER TABLE installation_module_version DROP FOREIGN KEY FK_39716106167B88B4');
        $this->addSql('ALTER TABLE installation_module_version DROP FOREIGN KEY FK_39716106DE590C98');
        $this->addSql('ALTER TABLE installation_docker_image_tag DROP FOREIGN KEY FK_24B2F5BB167B88B4');
        $this->addSql('ALTER TABLE installation_docker_image_tag DROP FOREIGN KEY FK_24B2F5BB30E50D4B');
        $this->addSql('DROP TABLE installation_package_version');
        $this->addSql('DROP TABLE installation_module_version');
        $this->addSql('DROP TABLE installation_docker_image_tag');
        $this->addSql('ALTER TABLE docker_image_tag DROP FOREIGN KEY FK_ED319DB59727D52');
        $this->addSql('ALTER TABLE docker_image_tag ADD CONSTRAINT FK_ED319DB59727D52 FOREIGN KEY (docker_image_id) REFERENCES docker_image (id)');
        $this->addSql('ALTER TABLE installation DROP FOREIGN KEY FK_1CBA6AB1D74ABC36');
        $this->addSql('ALTER TABLE installation DROP FOREIGN KEY FK_1CBA6AB11844E6B7');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB1D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE installation ADD CONSTRAINT FK_1CBA6AB11844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE git_tag DROP FOREIGN KEY FK_9F72C4EDBD359B2D');
        $this->addSql('ALTER TABLE git_tag ADD CONSTRAINT FK_9F72C4EDBD359B2D FOREIGN KEY (repo_id) REFERENCES git_repo (id)');
        $this->addSql('ALTER TABLE module_version DROP FOREIGN KEY FK_1467EDDAAFC2B591');
        $this->addSql('ALTER TABLE module_version ADD CONSTRAINT FK_1467EDDAAFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE package_version DROP FOREIGN KEY FK_3047B64FF44CABFF');
        $this->addSql('ALTER TABLE package_version CHANGE latest latest VARCHAR(25) DEFAULT NULL');
        $this->addSql('ALTER TABLE package_version ADD CONSTRAINT FK_3047B64FF44CABFF FOREIGN KEY (package_id) REFERENCES package (id)');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0BD74ABC36');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0BF6BD1646');
        $this->addSql('ALTER TABLE domain DROP FOREIGN KEY FK_A7A91E0B1844E6B7');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BD74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0BF6BD1646 FOREIGN KEY (site_id) REFERENCES site (id)');
        $this->addSql('ALTER TABLE domain ADD CONSTRAINT FK_A7A91E0B1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4D74ABC36');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E4167B88B4');
        $this->addSql('ALTER TABLE site DROP FOREIGN KEY FK_694309E41844E6B7');
        $this->addSql('ALTER TABLE site CHANGE installation_id installation_id BINARY(16) DEFAULT NULL COMMENT \'(DC2Type:ulid)\'');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4D74ABC36 FOREIGN KEY (detection_result_id) REFERENCES detection_result (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E4167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id)');
        $this->addSql('ALTER TABLE site ADD CONSTRAINT FK_694309E41844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE detection_result DROP FOREIGN KEY FK_9D26910F1844E6B7');
        $this->addSql('ALTER TABLE detection_result ADD CONSTRAINT FK_9D26910F1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id)');
        $this->addSql('ALTER TABLE package DROP abandoned, DROP warning');
    }
}
