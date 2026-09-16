<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260916083334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add deploy results, and record whether an installation is a git working copy or a release artifact';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE deploy_result (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, server_name VARCHAR(255) NOT NULL, root_dir VARCHAR(255) NOT NULL, repo_url VARCHAR(255) NOT NULL, tag VARCHAR(255) NOT NULL, commit VARCHAR(255) NOT NULL, pipeline_url VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, last_contact DATETIME NOT NULL, server_id BINARY(16) NOT NULL, UNIQUE INDEX UNIQ_3C6667FD1B862B8 (hash), UNIQUE INDEX deploy_server_hash_idx (server_id, hash), INDEX IDX_3C6667F1844E6B7 (server_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE deploy_result ADD CONSTRAINT FK_3C6667F1844E6B7 FOREIGN KEY (server_id) REFERENCES server (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE installation ADD code_source VARCHAR(10) DEFAULT NULL');

        // Every tag known before this point was read from a working copy on
        // disk by the harvester, so existing rows are git checkouts. Anything
        // without a tag stays unknown until a handler says otherwise.
        $this->addSql('UPDATE installation SET code_source = \'git\' WHERE git_tag_id IS NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE deploy_result DROP FOREIGN KEY FK_3C6667F1844E6B7');
        $this->addSql('DROP TABLE deploy_result');
        $this->addSql('ALTER TABLE installation DROP code_source');
    }
}
