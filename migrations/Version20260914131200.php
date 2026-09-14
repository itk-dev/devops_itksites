<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260914131200 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Move servers recorded as Ubuntu 25.04 to 26.04';
    }

    public function up(Schema $schema): void
    {
        // No server actually runs 25.04; the value was left behind on five
        // servers that have since moved to 26.04. Ubuntu 25.04 is being
        // dropped from App\Types\SystemType, so these rows would otherwise
        // hold a value the admin form cannot offer.
        $this->addSql("UPDATE server SET system = 'ubuntu2604' WHERE system = 'ubuntu2504'");
    }

    public function down(Schema $schema): void
    {
        // Once moved, these servers are indistinguishable from the ones
        // already recorded as 26.04, so the previous values cannot be restored.
        $this->throwIrreversibleMigration('Cannot tell the migrated servers from those already on Ubuntu 26.04.');
    }
}
