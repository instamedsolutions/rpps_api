<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250915094404 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add RPPS address';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE rpps_address (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', rpps_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', city_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', md5_address VARCHAR(32) NOT NULL, address VARCHAR(255) DEFAULT NULL, address_extension VARCHAR(255) DEFAULT NULL, zipcode VARCHAR(255) DEFAULT NULL, original_address LONGTEXT DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, coordinates POINT NOT NULL COMMENT \'(DC2Type:point)\', created_date DATETIME NOT NULL, import_id VARCHAR(20) NOT NULL, INDEX IDX_6EC5A0EA8BAC62AF (city_id), INDEX idx_rppsaddress_rpps (rpps_id), INDEX idx_rppsaddress_md5 (md5_address), UNIQUE INDEX uniq_rppsaddress_rpps_md5 (rpps_id, md5_address), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE rpps_address ADD CONSTRAINT FK_6EC5A0EAF4E1E022 FOREIGN KEY (rpps_id) REFERENCES rpps (id)');
        $this->addSql('ALTER TABLE rpps_address ADD CONSTRAINT FK_6EC5A0EA8BAC62AF FOREIGN KEY (city_id) REFERENCES city (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE rpps_address');
    }
}
