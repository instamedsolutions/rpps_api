<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210430234956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diseases (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', group_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', category_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', cim VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, hierarchy_level SMALLINT NOT NULL, sex SMALLINT DEFAULT NULL, lower_age_limit INT DEFAULT NULL, upper_age_limit INT DEFAULT NULL, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_F762064732EC6160 (cim), INDEX IDX_F7620647727ACA70 (parent_id), INDEX IDX_F7620647FE54D947 (group_id), INDEX IDX_F762064712469DE2 (category_id), INDEX diseases_index (cim), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diseases_group (id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', parent_id CHAR(36) DEFAULT NULL COMMENT \'(DC2Type:guid)\', cim VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`, created_date DATETIME NOT NULL, UNIQUE INDEX UNIQ_5B413DF032EC6160 (cim), INDEX IDX_5B413DF0727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases (id)');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647FE54D947 FOREIGN KEY (group_id) REFERENCES diseases_group (id)');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F762064712469DE2 FOREIGN KEY (category_id) REFERENCES diseases_group (id)');
        $this->addSql('ALTER TABLE diseases_group ADD CONSTRAINT FK_5B413DF0727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases_group (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647727ACA70');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647FE54D947');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F762064712469DE2');
        $this->addSql('ALTER TABLE diseases_group DROP FOREIGN KEY FK_5B413DF0727ACA70');
        $this->addSql('DROP TABLE diseases');
        $this->addSql('DROP TABLE diseases_group');
    }
}
