<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220103121354 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE jobs (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', code VARCHAR(10) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, mode VARCHAR(5) DEFAULT NULL, class VARCHAR(5) DEFAULT NULL, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_A8936DC577153098 (code), INDEX jobs_index (code), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE jobs');
    }
}
