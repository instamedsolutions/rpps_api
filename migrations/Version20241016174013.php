<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241016174013 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {

        $this->addSql('ALTER TABLE city ADD coordinates POINT DEFAULT NULL COMMENT \'(DC2Type:point)\'');
        $this->addSql('ALTER TABLE rpps ADD coordinates POINT DEFAULT NULL COMMENT \'(DC2Type:point)\'');
        $this->addSql('UPDATE city SET coordinates = POINT(longitude, latitude) WHERE latitude IS NOT NULL AND longitude IS NOT NULL');
        $this->addSql('UPDATE rpps SET coordinates = POINT(longitude, latitude) WHERE latitude IS NOT NULL AND longitude IS NOT NULL');
        $this->addSql('UPDATE city SET coordinates = POINT(0,0) WHERE longitude IS NULL OR latitude IS NULL');
        $this->addSql('UPDATE rpps SET coordinates = POINT(0,0) WHERE longitude IS NULL OR latitude IS NULL');
        $this->addSql('ALTER TABLE city CHANGE coordinates coordinates POINT NOT NULL COMMENT \'(DC2Type:point)\'');
        $this->addSql('ALTER TABLE rpps CHANGE coordinates coordinates POINT NOT NULL COMMENT \'(DC2Type:point)\'');

        $this->addSql('ALTER TABLE rpps ADD SPATIAL INDEX idx_coordinates (coordinates);');
        $this->addSql('ALTER TABLE city ADD SPATIAL INDEX idx_coordinates (coordinates);');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_coordinates ON rpps');
        $this->addSql('DROP INDEX idx_lng ON rpps');
        $this->addSql('ALTER TABLE city DROP coordinates');
        $this->addSql('ALTER TABLE rpps DROP coordinates');
    }
}
