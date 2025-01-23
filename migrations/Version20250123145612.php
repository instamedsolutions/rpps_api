<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250123145612 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for translation in cim 11 and specialties';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE translation (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', lang VARCHAR(5) NOT NULL, field VARCHAR(64) NOT NULL, translation LONGTEXT NOT NULL, created_date DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cim11_translation (cim11_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', translation_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_CEADE917DCD47F53 (cim11_id), INDEX IDX_CEADE9179CAA2B25 (translation_id), PRIMARY KEY(cim11_id, translation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cim11_modifier_value_translation (cim11_modifier_value_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', translation_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_3A114547FE0200B0 (cim11_modifier_value_id), INDEX IDX_3A1145479CAA2B25 (translation_id), PRIMARY KEY(cim11_modifier_value_id, translation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE specialty_translation (specialty_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', translation_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_584C3C2F9A353316 (specialty_id), INDEX IDX_584C3C2F9CAA2B25 (translation_id), PRIMARY KEY(specialty_id, translation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cim11_translation ADD CONSTRAINT FK_CEADE917DCD47F53 FOREIGN KEY (cim11_id) REFERENCES cim_11 (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cim11_translation ADD CONSTRAINT FK_CEADE9179CAA2B25 FOREIGN KEY (translation_id) REFERENCES translation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cim11_modifier_value_translation ADD CONSTRAINT FK_3A114547FE0200B0 FOREIGN KEY (cim11_modifier_value_id) REFERENCES cim_11_modifier_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cim11_modifier_value_translation ADD CONSTRAINT FK_3A1145479CAA2B25 FOREIGN KEY (translation_id) REFERENCES translation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE specialty_translation ADD CONSTRAINT FK_584C3C2F9A353316 FOREIGN KEY (specialty_id) REFERENCES specialty (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE specialty_translation ADD CONSTRAINT FK_584C3C2F9CAA2B25 FOREIGN KEY (translation_id) REFERENCES translation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE cim11_modifier_value_translation DROP FOREIGN KEY FK_3A1145479CAA2B25');
        $this->addSql('ALTER TABLE specialty_translation DROP FOREIGN KEY FK_584C3C2F9CAA2B25');
        $this->addSql('ALTER TABLE cim11_translation DROP FOREIGN KEY FK_CEADE9179CAA2B25');
        $this->addSql('DROP TABLE cim11_translation');
        $this->addSql('DROP TABLE translation');
        $this->addSql('DROP TABLE cim11_modifier_value_translation');
        $this->addSql('DROP TABLE specialty_translation');
    }

    public function isTransactional() : bool
    {
        return false;
    }

}
