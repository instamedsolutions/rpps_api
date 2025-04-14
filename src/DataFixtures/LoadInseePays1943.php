<?php

namespace App\DataFixtures;

use App\Entity\InseePays1943;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadInseePays1943 extends Fixture
{
    private const string IMPORT_ID = 'import_1';

    public function load(ObjectManager $manager): void
    {
        $pays = [
            [
                'code' => '99100',
                'libelle' => 'France',
                'libelleOfficiel' => 'République française',
                'dateDebut' => '1943-01-01',
            ],
            [
                'code' => '99109',
                'libelle' => 'Allemagne',
                'libelleOfficiel' => 'Reich allemand',
                'dateDebut' => '1943-01-01',
                'dateFin' => '1990-10-02',
            ],
            [
                'code' => '99110',
                'libelle' => 'RDA',
                'libelleOfficiel' => 'République démocratique allemande',
                'dateDebut' => '1949-10-07',
                'dateFin' => '1990-10-02',
            ],
            [
                'code' => '99134',
                'libelle' => 'Espagne',
                'libelleOfficiel' => 'Royaume d’Espagne',
                'dateDebut' => '1943-01-01',
            ],
            [
                'code' => '99216',
                'libelle' => 'Chine',
                'libelleOfficiel' => 'République de Chine',
                'dateDebut' => '1943-01-01',
                'dateFin' => '1949-09-30',
            ],
            [
                'code' => '99230',
                'libelle' => 'Hong Kong',
                'libelleOfficiel' => 'Colonie britannique de Hong Kong',
                'codeRattachement' => '99401',
                'dateDebut' => '1943-01-01',
                'dateFin' => '1997-06-30',
            ],
            [
                'code' => '99217',
                'libelle' => 'Japon',
                'libelleOfficiel' => 'Empire du Japon',
                'dateDebut' => '1943-01-01',
            ],
            [
                'code' => '99302',
                'libelle' => 'Libéria',
                'libelleOfficiel' => 'République du Libéria',
                'dateDebut' => '1847-07-26',
            ],
            [
                'code' => '99401',
                'libelle' => 'Canada',
                'libelleOfficiel' => 'Dominion du Canada',
                'dateDebut' => '1943-01-01',
            ],
            [
                'code' => '99399',
                'libelle' => 'Territoire fictif',
                'libelleOfficiel' => 'État temporaire',
                'dateDebut' => '1950-01-01',
                'dateFin' => '1960-12-31',
            ],
            [
                'code' => '99223',
                'libelle' => 'Indes britanniques',
                'libelleOfficiel' => 'Inde, Ceylan, Balouchistan britannique',
                'codeRattachement' => '99132',
                'dateDebut' => '1943-01-01',
                'dateFin' => '1947-08-14',
            ],
            [
                'code' => '99223',
                'libelle' => 'Inde',
                'libelleOfficiel' => 'Dominion de l’Inde',
                'dateDebut' => '1947-08-14',
                'dateFin' => '1950-01-26',
            ],
            [
                'code' => '99223',
                'libelle' => 'Inde',
                'libelleOfficiel' => 'République de l’Inde',
                'dateDebut' => '1950-01-26',
                'dateFin' => null,
            ],
            [
                'code' => '99231',
                'libelle' => 'Indes néerlandaises',
                'libelleOfficiel' => 'Java, Sumatra, Bornéo (partie néerlandaise), Célèbes, Moluques, îles de la Sonde',
                'codeRattachement' => '99135',
                'dateDebut' => '1943-01-01',
                'dateFin' => '1945-01-01',
            ],
        ];

        foreach ($pays as $row) {
            $entity = new InseePays1943();
            $entity->setCodePays($row['code']);
            $entity->setLibelleCog($row['libelle']);
            $entity->setLibelleOfficiel($row['libelleOfficiel']);
            $entity->setDateDebut(new DateTime($row['dateDebut']));

            if (isset($row['codeRattachement'])) {
                $entity->setCodeRattachement($row['codeRattachement']);
            }

            if (isset($row['dateFin'])) {
                $entity->setDateFin(new DateTime($row['dateFin']));
            }

            $entity->setImportId(self::IMPORT_ID);

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
