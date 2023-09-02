<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220816133228 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Update relation between diseases and groups';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F762064712469DE2');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647727ACA70');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647FE54D947');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F762064712469DE2 FOREIGN KEY (category_id) REFERENCES diseases_group (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647FE54D947 FOREIGN KEY (group_id) REFERENCES diseases_group (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE diseases_group DROP FOREIGN KEY FK_5B413DF0727ACA70');
        $this->addSql('ALTER TABLE diseases_group ADD CONSTRAINT FK_5B413DF0727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases_group (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647727ACA70');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F7620647FE54D947');
        $this->addSql('ALTER TABLE diseases DROP FOREIGN KEY FK_F762064712469DE2');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases (id)');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F7620647FE54D947 FOREIGN KEY (group_id) REFERENCES diseases_group (id)');
        $this->addSql('ALTER TABLE diseases ADD CONSTRAINT FK_F762064712469DE2 FOREIGN KEY (category_id) REFERENCES diseases_group (id)');
        $this->addSql('ALTER TABLE diseases_group DROP FOREIGN KEY FK_5B413DF0727ACA70');
        $this->addSql('ALTER TABLE diseases_group ADD CONSTRAINT FK_5B413DF0727ACA70 FOREIGN KEY (parent_id) REFERENCES diseases_group (id)');
    }
}
