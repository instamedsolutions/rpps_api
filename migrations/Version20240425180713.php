<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240425180713 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for cim-11';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE cim_11 (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(16) NOT NULL, name LONGTEXT NOT NULL, hierarchy_level SMALLINT NOT NULL, who_id VARCHAR(32) NOT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', cim10_code VARCHAR(32) DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_92AF88B077153098 (code), UNIQUE INDEX UNIQ_92AF88B0F4E25B21 (who_id), INDEX IDX_92AF88B0727ACA70 (parent_id), INDEX IDX_92AF88B077153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cim_11_modifier (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', cim11_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', type VARCHAR(64) NOT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, INDEX IDX_AC88D6A0DCD47F53 (cim11_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE cim_11_modifier_value (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(16) NOT NULL, name LONGTEXT NOT NULL, who_id VARCHAR(32) NOT NULL, synonyms LONGTEXT NOT NULL COMMENT \'(DC2Type:simple_array)\', created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, UNIQUE INDEX UNIQ_7069156F77153098 (code), UNIQUE INDEX UNIQ_7069156FF4E25B21 (who_id), INDEX IDX_7069156F77153098 (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cim_11 ADD CONSTRAINT FK_92AF88B0727ACA70 FOREIGN KEY (parent_id) REFERENCES cim_11 (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE cim_11_modifier ADD CONSTRAINT FK_AC88D6A0DCD47F53 FOREIGN KEY (cim11_id) REFERENCES cim_11 (id) ON DELETE CASCADE ');
        $this->addSql('CREATE TABLE cim11_modifier_value_cim11_modifier (cim11_modifier_value_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', cim11_modifier_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_7BBF7554FE0200B0 (cim11_modifier_value_id), INDEX IDX_7BBF7554AABD592B (cim11_modifier_id), PRIMARY KEY(cim11_modifier_value_id, cim11_modifier_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE cim11_modifier_value_cim11_modifier ADD CONSTRAINT FK_7BBF7554FE0200B0 FOREIGN KEY (cim11_modifier_value_id) REFERENCES cim_11_modifier_value (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cim11_modifier_value_cim11_modifier ADD CONSTRAINT FK_7BBF7554AABD592B FOREIGN KEY (cim11_modifier_id) REFERENCES cim_11_modifier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE cim_11_modifier CHANGE type type VARCHAR(64) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cim_11 DROP FOREIGN KEY FK_92AF88B0727ACA70');
        $this->addSql('ALTER TABLE cim_11_modifier DROP FOREIGN KEY FK_AC88D6A0DCD47F53');
        $this->addSql('DROP TABLE cim11_modifier_value_cim11_modifier');
        $this->addSql('DROP TABLE cim_11');
        $this->addSql('DROP TABLE cim_11_modifier');
        $this->addSql('DROP TABLE cim_11_modifier_value');
    }

    public function isTransactional() : bool
    {
        return false;
    }

}
