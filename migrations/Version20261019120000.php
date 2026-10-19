<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Make room for git checkout versions on installations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE installation CHANGE php_version php_version VARCHAR(255) DEFAULT NULL, CHANGE composer_version composer_version VARCHAR(255) DEFAULT NULL, CHANGE framework_version framework_version VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE installation CHANGE php_version php_version VARCHAR(10) DEFAULT NULL, CHANGE composer_version composer_version VARCHAR(10) DEFAULT NULL, CHANGE framework_version framework_version VARCHAR(10) DEFAULT NULL');
    }
}
