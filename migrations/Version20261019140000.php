<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Store the registry of docker images';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE docker_image ADD registry VARCHAR(255) NOT NULL DEFAULT 'docker.io' AFTER id");
        $this->addSql('ALTER TABLE docker_image ALTER registry DROP DEFAULT');
        // Images on another registry were stored as organization "ghcr.io" and
        // repository "<organization>", without the rest; a replay fills it in.
        // MariaDB assigns left to right, so each column reads the old value it needs.
        $this->addSql("UPDATE docker_image SET registry = organization, organization = repository, repository = '' WHERE organization LIKE '%.%' OR organization LIKE '%:%' OR organization = 'localhost'");
        $this->addSql('DROP INDEX organization_repository ON docker_image');
        $this->addSql('CREATE UNIQUE INDEX registry_organization_repository ON docker_image (registry, organization, repository)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("UPDATE docker_image SET repository = organization, organization = registry WHERE registry <> 'docker.io'");
        $this->addSql('DROP INDEX registry_organization_repository ON docker_image');
        $this->addSql('CREATE UNIQUE INDEX organization_repository ON docker_image (organization, repository)');
        $this->addSql('ALTER TABLE docker_image DROP registry');
    }
}
