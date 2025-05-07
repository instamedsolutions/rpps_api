<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507004714 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for original address in RPPS db';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rpps ADD original_address LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rpps DROP original_address');
    }
}
