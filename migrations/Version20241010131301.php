<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241010131301 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for address extension, latitude and longitude';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rpps ADD address_extension VARCHAR(255) DEFAULT NULL, ADD latitude DOUBLE PRECISION DEFAULT NULL, ADD longitude DOUBLE PRECISION DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE rpps DROP address_extension, DROP latitude, DROP longitude');
    }
}
