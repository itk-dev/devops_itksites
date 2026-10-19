<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019140000 extends AbstractMigration
{
    private const string KEEP_TAG = '(SELECT docker_image_id, tag, MIN(id) AS id FROM docker_image_tag GROUP BY docker_image_id, tag)';

    public function getDescription(): string
    {
        return 'Store the registry of docker images, and one tag per image and tag';
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

        // Tags were also keyed by the Compose service name. Move each
        // installation's link to the tag row that is kept, then drop the others.
        $this->addSql('INSERT IGNORE INTO installation_docker_image_tag (installation_id, docker_image_tag_id)
            SELECT idit.installation_id, keep.id FROM installation_docker_image_tag idit
            JOIN docker_image_tag t ON t.id = idit.docker_image_tag_id
            JOIN '.self::KEEP_TAG.' keep ON keep.docker_image_id = t.docker_image_id AND keep.tag = t.tag
            WHERE t.id <> keep.id');
        $this->addSql('DELETE t FROM docker_image_tag t
            JOIN '.self::KEEP_TAG.' keep ON keep.docker_image_id = t.docker_image_id AND keep.tag = t.tag
            WHERE t.id <> keep.id');
        $this->addSql('DROP INDEX dockerImage_name_tag ON docker_image_tag');
        $this->addSql('ALTER TABLE docker_image_tag DROP name');
        $this->addSql('CREATE UNIQUE INDEX dockerImage_tag ON docker_image_tag (docker_image_id, tag)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX dockerImage_tag ON docker_image_tag');
        $this->addSql("ALTER TABLE docker_image_tag ADD name VARCHAR(50) NOT NULL DEFAULT ''");
        $this->addSql('ALTER TABLE docker_image_tag ALTER name DROP DEFAULT');
        $this->addSql('CREATE UNIQUE INDEX dockerImage_name_tag ON docker_image_tag (docker_image_id, name, tag)');

        $this->addSql("UPDATE docker_image SET repository = organization, organization = registry WHERE registry <> 'docker.io'");
        $this->addSql('DROP INDEX registry_organization_repository ON docker_image');
        $this->addSql('CREATE UNIQUE INDEX organization_repository ON docker_image (organization, repository)');
        $this->addSql('ALTER TABLE docker_image DROP registry');
    }
}
