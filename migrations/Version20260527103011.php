<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260527103011 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Introduce Project + CodeOwner entities; slim SecurityContract to nested-agreement fields.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE code_owner (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, economics_id INT NOT NULL, name VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_2335FF304416F7E8 (economics_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project (id BINARY(16) NOT NULL, created_at DATETIME NOT NULL, modified_at DATETIME NOT NULL, created_by VARCHAR(255) DEFAULT \'\' NOT NULL, modified_by VARCHAR(255) DEFAULT \'\' NOT NULL, economics_id INT NOT NULL, name VARCHAR(255) NOT NULL, leantime_id VARCHAR(255) DEFAULT NULL, leantime_url VARCHAR(255) DEFAULT NULL, UNIQUE INDEX UNIQ_2FB3D0EE4416F7E8 (economics_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project_code_owner (project_id BINARY(16) NOT NULL, code_owner_id BINARY(16) NOT NULL, INDEX IDX_3B938402166D1F9C (project_id), INDEX IDX_3B93840287BD19D2 (code_owner_id), PRIMARY KEY (project_id, code_owner_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('CREATE TABLE project_git_repo (project_id BINARY(16) NOT NULL, git_repo_id BINARY(16) NOT NULL, INDEX IDX_CB848708166D1F9C (project_id), INDEX IDX_CB8487083E8A2A0D (git_repo_id), PRIMARY KEY (project_id, git_repo_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE project_code_owner ADD CONSTRAINT FK_3B938402166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_code_owner ADD CONSTRAINT FK_3B93840287BD19D2 FOREIGN KEY (code_owner_id) REFERENCES code_owner (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_git_repo ADD CONSTRAINT FK_CB848708166D1F9C FOREIGN KEY (project_id) REFERENCES project (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE project_git_repo ADD CONSTRAINT FK_CB8487083E8A2A0D FOREIGN KEY (git_repo_id) REFERENCES git_repo (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE security_contract ADD project_id BINARY(16) DEFAULT NULL, DROP project_name, DROP client_name, DROP leantime_url, DROP git_repos, DROP project_tracker_key, DROP quarterly_hours, DROP cybersecurity_price, DROP cybersecurity_note');
        $this->addSql('ALTER TABLE security_contract ADD CONSTRAINT FK_8AE4AF8B166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
        $this->addSql('CREATE INDEX IDX_8AE4AF8B166D1F9C ON security_contract (project_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE project_code_owner DROP FOREIGN KEY FK_3B938402166D1F9C');
        $this->addSql('ALTER TABLE project_code_owner DROP FOREIGN KEY FK_3B93840287BD19D2');
        $this->addSql('ALTER TABLE project_git_repo DROP FOREIGN KEY FK_CB848708166D1F9C');
        $this->addSql('ALTER TABLE project_git_repo DROP FOREIGN KEY FK_CB8487083E8A2A0D');
        $this->addSql('DROP TABLE code_owner');
        $this->addSql('DROP TABLE project');
        $this->addSql('DROP TABLE project_code_owner');
        $this->addSql('DROP TABLE project_git_repo');
        $this->addSql('ALTER TABLE security_contract DROP FOREIGN KEY FK_8AE4AF8B166D1F9C');
        $this->addSql('DROP INDEX IDX_8AE4AF8B166D1F9C ON security_contract');
        $this->addSql('ALTER TABLE security_contract ADD project_name VARCHAR(255) NOT NULL, ADD client_name VARCHAR(255) DEFAULT NULL, ADD leantime_url VARCHAR(255) DEFAULT NULL, ADD git_repos LONGTEXT DEFAULT NULL, ADD project_tracker_key VARCHAR(255) DEFAULT NULL, ADD quarterly_hours DOUBLE PRECISION DEFAULT NULL, ADD cybersecurity_price DOUBLE PRECISION DEFAULT NULL, ADD cybersecurity_note LONGTEXT DEFAULT NULL, DROP project_id');
    }
}
