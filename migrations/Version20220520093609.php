<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220520093609 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE module (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, package VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, display_name VARCHAR(255) DEFAULT NULL, enabled TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE module_version (id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', module_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', modified_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, version VARCHAR(50) DEFAULT NULL, INDEX IDX_1467EDDAAFC2B591 (module_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE module_version_installation (module_version_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', installation_id BINARY(16) NOT NULL COMMENT \'(DC2Type:ulid)\', INDEX IDX_54CEDC77DE590C98 (module_version_id), INDEX IDX_54CEDC77167B88B4 (installation_id), PRIMARY KEY(module_version_id, installation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE module_version ADD CONSTRAINT FK_1467EDDAAFC2B591 FOREIGN KEY (module_id) REFERENCES module (id)');
        $this->addSql('ALTER TABLE module_version_installation ADD CONSTRAINT FK_54CEDC77DE590C98 FOREIGN KEY (module_version_id) REFERENCES module_version (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE module_version_installation ADD CONSTRAINT FK_54CEDC77167B88B4 FOREIGN KEY (installation_id) REFERENCES installation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE module_version DROP FOREIGN KEY FK_1467EDDAAFC2B591');
        $this->addSql('ALTER TABLE module_version_installation DROP FOREIGN KEY FK_54CEDC77DE590C98');
        $this->addSql('DROP TABLE module');
        $this->addSql('DROP TABLE module_version');
        $this->addSql('DROP TABLE module_version_installation');
    }
}
