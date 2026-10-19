<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260922212043 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Link modules and module versions to their Composer package and package version';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE module ADD composer_package_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE module ADD CONSTRAINT FK_C2426281ACE726A FOREIGN KEY (composer_package_id) REFERENCES package (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_C2426281ACE726A ON module (composer_package_id)');
        $this->addSql('ALTER TABLE module_version ADD composer_package_version_id BINARY(16) DEFAULT NULL');
        $this->addSql('ALTER TABLE module_version ADD CONSTRAINT FK_1467EDDADA07E29E FOREIGN KEY (composer_package_version_id) REFERENCES package_version (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_1467EDDADA07E29E ON module_version (composer_package_version_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE module DROP FOREIGN KEY FK_C2426281ACE726A');
        $this->addSql('DROP INDEX IDX_C2426281ACE726A ON module');
        $this->addSql('ALTER TABLE module DROP composer_package_id');
        $this->addSql('ALTER TABLE module_version DROP FOREIGN KEY FK_1467EDDADA07E29E');
        $this->addSql('DROP INDEX IDX_1467EDDADA07E29E ON module_version');
        $this->addSql('ALTER TABLE module_version DROP composer_package_version_id');
    }
}
