<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240722071835 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for fullnames';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE rpps ADD full_name VARCHAR(255) DEFAULT NULL, ADD full_name_inversed VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE INDEX full_name_index ON rpps (full_name)');
        $this->addSql('CREATE INDEX full_name_inversed_index ON rpps (full_name_inversed)');

        $this->addSql('UPDATE rpps SET full_name_inversed = CONCAT_WS(" ", last_name, first_name)');
        $this->addSql('UPDATE rpps SET full_name = CONCAT_WS(" ", first_name, last_name)');
        $this->addSql('ALTER TABLE rpps CHANGE full_name full_name VARCHAR(255) NOT NULL, CHANGE full_name_inversed full_name_inversed VARCHAR(255) NOT NULL');

    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX full_name_index ON rpps');
        $this->addSql('DROP INDEX full_name_inversed_index ON rpps');
        $this->addSql('ALTER TABLE rpps DROP full_name, DROP full_name_inversed');
    }
}
