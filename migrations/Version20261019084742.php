<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019084742 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the drupal.org project on modules';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE module ADD project VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX IDX_C2426282FB3D0EE ON module (project)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX IDX_C2426282FB3D0EE ON module');
        $this->addSql('ALTER TABLE module DROP project');
    }
}
