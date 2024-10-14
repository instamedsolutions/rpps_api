<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919103509 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE city (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', department_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', main_city_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', canonical VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, raw_name VARCHAR(255) DEFAULT NULL, sub_city_name VARCHAR(255) DEFAULT NULL, raw_sub_name VARCHAR(255) DEFAULT NULL, insee_code VARCHAR(8) NOT NULL, postal_code VARCHAR(12) NOT NULL, latitude NUMERIC(22, 16) DEFAULT NULL, longitude NUMERIC(22, 16) DEFAULT NULL, population INT DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_2D5B0234AFD2F094 (canonical), INDEX IDX_2D5B0234AE80F5DF (department_id), INDEX IDX_2D5B02347FE7CB46 (main_city_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE department (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', region_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', chef_lieu_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, code_department VARCHAR(255) NOT NULL, department_type VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_CD1DE18AE5B31C6C (code_department), INDEX IDX_CD1DE18A98260155 (region_id), UNIQUE INDEX UNIQ_CD1DE18AAD611528 (chef_lieu_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, code_region VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_F62F17670E4A9D4 (code_region), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B0234AE80F5DF FOREIGN KEY (department_id) REFERENCES department (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE city ADD CONSTRAINT FK_2D5B02347FE7CB46 FOREIGN KEY (main_city_id) REFERENCES city (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18A98260155 FOREIGN KEY (region_id) REFERENCES region (id) ON DELETE CASCADE ');
        $this->addSql('ALTER TABLE department ADD CONSTRAINT FK_CD1DE18AAD611528 FOREIGN KEY (chef_lieu_id) REFERENCES city (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE rpps ADD city_entity_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE rpps ADD CONSTRAINT FK_52B0862B25716148 FOREIGN KEY (city_entity_id) REFERENCES city (id) ON DELETE CASCADE;');
        $this->addSql('CREATE INDEX IDX_52B0862B25716148 ON rpps (city_entity_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B02347FE7CB46');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18AAD611528');
        $this->addSql('ALTER TABLE rpps DROP FOREIGN KEY FK_52B0862B25716148');
        $this->addSql('ALTER TABLE city DROP FOREIGN KEY FK_2D5B0234AE80F5DF');
        $this->addSql('ALTER TABLE department DROP FOREIGN KEY FK_CD1DE18A98260155');
        $this->addSql('DROP TABLE city');
        $this->addSql('DROP TABLE department');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP INDEX IDX_52B0862B25716148 ON rpps');
        $this->addSql('ALTER TABLE rpps DROP city_entity_id');
    }
}
