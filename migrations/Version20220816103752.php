<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220816103752 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add import id and update some tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergens ADD import_id VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE ccam ADD import_id VARCHAR(20) NOT NULL, CHANGE name name LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE ccam_group ADD import_id VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE diseases ADD import_id VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE diseases_group ADD import_id VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE drugs ADD import_id VARCHAR(20) NOT NULL, CHANGE generic_type generic_type LONGTEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE rpps ADD import_id VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE allergens DROP import_id');
        $this->addSql('ALTER TABLE ccam DROP import_id, CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE ccam_group DROP import_id');
        $this->addSql('ALTER TABLE diseases DROP import_id');
        $this->addSql('ALTER TABLE diseases_group DROP import_id');
        $this->addSql('ALTER TABLE drugs DROP import_id, CHANGE generic_type generic_type VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE rpps DROP import_id');
    }
}
