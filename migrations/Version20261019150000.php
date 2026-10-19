<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20261019150000 extends AbstractMigration
{
    private const string KEEP = '(SELECT docker_image_id, tag, MIN(id) AS id FROM docker_image_tag GROUP BY docker_image_id, tag)';

    public function getDescription(): string
    {
        return 'One docker image tag per image and tag, without the Compose service name';
    }

    public function up(Schema $schema): void
    {
        // Move each installation's link to the tag row that is kept, then drop the others.
        $this->addSql('INSERT IGNORE INTO installation_docker_image_tag (installation_id, docker_image_tag_id)
            SELECT idit.installation_id, keep.id FROM installation_docker_image_tag idit
            JOIN docker_image_tag t ON t.id = idit.docker_image_tag_id
            JOIN '.self::KEEP.' keep ON keep.docker_image_id = t.docker_image_id AND keep.tag = t.tag
            WHERE t.id <> keep.id');
        $this->addSql('DELETE t FROM docker_image_tag t
            JOIN '.self::KEEP.' keep ON keep.docker_image_id = t.docker_image_id AND keep.tag = t.tag
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
    }
}
