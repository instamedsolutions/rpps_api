<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;


final class Version20250124085944 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for allergens translations';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE allergen_translation (allergen_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', translation_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', INDEX IDX_5E190A826E775A4A (allergen_id), INDEX IDX_5E190A829CAA2B25 (translation_id), PRIMARY KEY(allergen_id, translation_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE allergen_translation ADD CONSTRAINT FK_5E190A826E775A4A FOREIGN KEY (allergen_id) REFERENCES allergens (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE allergen_translation ADD CONSTRAINT FK_5E190A829CAA2B25 FOREIGN KEY (translation_id) REFERENCES translation (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE allergen_translation');
    }
}
