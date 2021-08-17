<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210816034015 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE ccam (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', group_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', category_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, description LONGTEXT NOT NULL COLLATE `utf8mb4_unicode_ci`, rate1 SMALLINT DEFAULT NULL, rate2 SMALLINT DEFAULT NULL, modifiers JSON NOT NULL, regroupement_code VARCHAR(4) NOT NULL, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_DA3B58B77153098 (code), INDEX IDX_DA3B58BFE54D947 (group_id), INDEX IDX_DA3B58B12469DE2 (category_id), INDEX ccam_index (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ccam_group (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_1CAEEE9E77153098 (code), INDEX IDX_1CAEEE9E727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE ccam CHANGE group_id group_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', CHANGE category_id category_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE ccam ADD CONSTRAINT FK_DA3B58BFE54D947 FOREIGN KEY (group_id) REFERENCES ccam_group (id)');
        $this->addSql('ALTER TABLE ccam ADD CONSTRAINT FK_DA3B58B12469DE2 FOREIGN KEY (category_id) REFERENCES ccam_group (id)');
        $this->addSql('ALTER TABLE ccam_group ADD CONSTRAINT FK_1CAEEE9E727ACA70 FOREIGN KEY (parent_id) REFERENCES ccam_group (id)');
        $this->addSql('ALTER TABLE ccam CHANGE description description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE rate1 rate1 DOUBLE PRECISION DEFAULT NULL, CHANGE rate2 rate2 DOUBLE PRECISION DEFAULT NULL');
        $this->addSql('ALTER TABLE ccam_group ADD description LONGTEXT DEFAULT NULL COLLATE `utf8mb4_unicode_ci`');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE ccam DROP FOREIGN KEY FK_DA3B58BFE54D947');
        $this->addSql('ALTER TABLE ccam DROP FOREIGN KEY FK_DA3B58B12469DE2');
        $this->addSql('ALTER TABLE ccam_group DROP FOREIGN KEY FK_1CAEEE9E727ACA70');
        $this->addSql('DROP TABLE ccam');
        $this->addSql('DROP TABLE ccam_group');
        $this->addSql('ALTER TABLE allergens CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE diseases CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE diseases_group CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE drugs CHANGE name name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_0900_ai_ci`');
        $this->addSql('ALTER TABLE rpps CHANGE last_name last_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`, CHANGE first_name first_name VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_0900_ai_ci`');
    }
}
