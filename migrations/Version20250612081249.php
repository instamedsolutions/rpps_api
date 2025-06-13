<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250612081249 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for Anesthetist rates';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ccam ADD anesthetist_rate1 DOUBLE PRECISION DEFAULT NULL, ADD anesthetist_rate2 DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE ccam DROP anesthetist_rate1, DROP anesthetist_rate2');
    }
}
