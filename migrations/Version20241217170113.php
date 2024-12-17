<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241217170113 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add support for main specialties';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE specialty ADD main BOOLEAN NOT NULL');
        $this->addSql('UPDATE specialty SET main = 1 WHERE canonical IN (:canonicals)', [
            'canonicals' => ['cardiologue', 'chirurgien-general', 'dermatologue', 'geriatre', 'urgentiste', 'medecin-generaliste', 'neurologue', 'oncologue', 'ophtalmologue', 'pneumologue', 'radiologue', 'sage-femme', 'urologue']
        ], [
            'canonicals' => Connection::PARAM_STR_ARRAY
        ]);
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE specialty DROP main');
    }

    public function isTransactional(): bool
    {
        return true;
    }

}
