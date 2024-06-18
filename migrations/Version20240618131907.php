<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20240618131907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add speciialty index';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE INDEX specialty_index ON rpps (specialty)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX specialty_index ON rpps');
    }

    public function isTransactional() : bool
    {
        return false;
    }

}
