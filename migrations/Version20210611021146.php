<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210611021146 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE allergens (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', allergen_code VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_0900_ai_ci`, allergen_group VARCHAR(255) NOT NULL, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_67F79FB49F50571C (allergen_code), INDEX allergens_index (allergen_code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE allergens');
    }
}
