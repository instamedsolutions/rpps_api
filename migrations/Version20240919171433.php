<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919171433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE specialty (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, canonical VARCHAR(255) NOT NULL, specialist_name VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, is_paramedical TINYINT NOT NULL, UNIQUE INDEX UNIQ_E066A6ECAFD2F094 (canonical), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialty_links (specialty_source CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', specialty_target CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_72F484346DF165B5 (specialty_source), INDEX IDX_72F484347414353A (specialty_target), PRIMARY KEY(specialty_source, specialty_target)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE specialty_links ADD CONSTRAINT FK_72F484346DF165B5 FOREIGN KEY (specialty_source) REFERENCES specialty (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE specialty_links ADD CONSTRAINT FK_72F484347414353A FOREIGN KEY (specialty_target) REFERENCES specialty (id) ON DELETE CASCADE');

        $this->addSql('ALTER TABLE rpps ADD specialty_entity_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE rpps ADD CONSTRAINT FK_52B0862B63FDC5F2 FOREIGN KEY (specialty_entity_id) REFERENCES specialty (id)');
        $this->addSql('CREATE INDEX IDX_52B0862B63FDC5F2 ON rpps (specialty_entity_id)');
        $this->addSql('CREATE INDEX specialty_name_index ON specialty (name)');

        $this->addSql('ALTER TABLE rpps ADD canonical VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_52B0862BAFD2F094 ON rpps (canonical)');
        $this->addSql('CREATE INDEX canonical_index ON rpps (canonical)');

        $this->addSql('CREATE INDEX last_name_index ON rpps (last_name)');
        $this->addSql('CREATE INDEX specialty_is_paramedical_index ON specialty (is_paramedical)');
        $this->addSql('CREATE INDEX idx_specialty_composite ON specialty (is_paramedical,id)');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE specialty_links DROP FOREIGN KEY FK_72F484346DF165B5');
        $this->addSql('ALTER TABLE specialty_links DROP FOREIGN KEY FK_72F484347414353A');
        $this->addSql('DROP TABLE specialty');
        $this->addSql('DROP TABLE specialty_links');

        $this->addSql('ALTER TABLE rpps DROP FOREIGN KEY FK_52B0862B63FDC5F2');
        $this->addSql('DROP INDEX IDX_52B0862B63FDC5F2 ON rpps');
        $this->addSql('ALTER TABLE rpps DROP specialty_entity_id');
        $this->addSql('DROP INDEX specialty_name_index ON specialty');

        $this->addSql('DROP INDEX UNIQ_52B0862BAFD2F094 ON rpps');
        $this->addSql('DROP INDEX canonical_index ON rpps');
        $this->addSql('ALTER TABLE rpps DROP canonical');
        $this->addSql('DROP INDEX specialty_is_paramedical_index ON specialty');
        $this->addSql('DROP INDEX idx_specialty_composite ON specialty');

        $this->addSql('DROP INDEX last_name_index ON rpps');
    }
}
