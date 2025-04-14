<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250313102050 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add INSEE tables';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE insee_commune (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', type_commune VARCHAR(4) DEFAULT NULL, code_commune VARCHAR(5) DEFAULT NULL, code_region VARCHAR(2) DEFAULT NULL, code_departement VARCHAR(3) DEFAULT NULL, code_collectivite VARCHAR(4) DEFAULT NULL, code_arrondissement VARCHAR(4) DEFAULT NULL, type_nom_en_clair VARCHAR(1) DEFAULT NULL, nom_en_clair VARCHAR(200) NOT NULL, nom_en_clair_typo VARCHAR(200) DEFAULT NULL, nom_en_clair_avec_article VARCHAR(200) DEFAULT NULL, code_canton VARCHAR(5) DEFAULT NULL, code_commune_parente VARCHAR(5) DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE insee_commune_1943 (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', code_commune VARCHAR(5) DEFAULT NULL, type_nom_en_clair VARCHAR(1) DEFAULT NULL, nom_majuscule VARCHAR(200) DEFAULT NULL, nom_typographie VARCHAR(200) DEFAULT NULL, nom_avec_article VARCHAR(200) DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE insee_commune_event (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', mod_event VARCHAR(2) DEFAULT NULL, date_eff DATE DEFAULT NULL, type_commune_av VARCHAR(4) DEFAULT NULL, code_commune_av VARCHAR(5) DEFAULT NULL, tncc_av VARCHAR(1) DEFAULT NULL, nom_majuscule_av VARCHAR(200) DEFAULT NULL, nom_typo_av VARCHAR(200) DEFAULT NULL, nom_article_av VARCHAR(200) DEFAULT NULL, type_commune_ap VARCHAR(4) DEFAULT NULL, code_commune_ap VARCHAR(5) DEFAULT NULL, tncc_ap VARCHAR(1) DEFAULT NULL, nom_majuscule_ap VARCHAR(200) DEFAULT NULL, nom_typo_ap VARCHAR(200) DEFAULT NULL, nom_article_ap VARCHAR(200) DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE insee_pays (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', code_pays VARCHAR(5) DEFAULT NULL, code_actualite VARCHAR(1) DEFAULT NULL, code_rattachement VARCHAR(5) DEFAULT NULL, annee_apparition VARCHAR(4) DEFAULT NULL, libelle_cog VARCHAR(70) DEFAULT NULL, libelle_officiel VARCHAR(200) DEFAULT NULL, code_iso2 VARCHAR(2) DEFAULT NULL, code_iso3 VARCHAR(3) DEFAULT NULL, code_iso_num3 VARCHAR(3) DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE insee_pays_1943 (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', code_pays VARCHAR(5) DEFAULT NULL, code_rattachement VARCHAR(5) DEFAULT NULL, libelle_cog VARCHAR(70) DEFAULT NULL, libelle_officiel VARCHAR(200) DEFAULT NULL, date_debut DATE DEFAULT NULL, date_fin DATE DEFAULT NULL, created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE insee_commune');
        $this->addSql('DROP TABLE insee_commune_1943');
        $this->addSql('DROP TABLE insee_commune_event');
        $this->addSql('DROP TABLE insee_pays');
        $this->addSql('DROP TABLE insee_pays_1943');
    }
}
