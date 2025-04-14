<?php

namespace App\DataFixtures;

use App\Entity\InseePays;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadInseePays extends Fixture
{
    private const string IMPORT_ID = 'import_1';

    public function load(ObjectManager $manager): void
    {
        $pays = [
            [
                'code' => '99100',
                'libelle' => 'France',
                'libelleOfficiel' => 'République française',
                'alpha2' => 'FR',
                'alpha3' => 'FRA',
                'codeNumerique' => '250',
                'codeActualite' => '1',
                'anneeApparition' => '1792',
            ],
            [
                'code' => '99109',
                'libelle' => 'Allemagne',
                'libelleOfficiel' => 'République fédérale d’Allemagne',
                'alpha2' => 'DE',
                'alpha3' => 'DEU',
                'codeNumerique' => '276',
                'anneeApparition' => '1990',
            ],
            [
                'code' => '99134',
                'libelle' => 'Espagne',
                'libelleOfficiel' => 'Royaume d’Espagne',
                'alpha2' => 'ES',
                'alpha3' => 'ESP',
                'codeNumerique' => '724',
            ],
            [
                'code' => '99132',
                'libelle' => 'Royaume-Uni',
                'libelleOfficiel' => 'Royaume-Uni de Grande-Bretagne et d’Irlande du Nord',
                'alpha2' => 'GB',
                'alpha3' => 'GBR',
                'codeNumerique' => '826',
            ],
            [
                'code' => '99216',
                'libelle' => 'Chine',
                'libelleOfficiel' => 'République populaire de Chine',
                'alpha2' => 'CN',
                'alpha3' => 'CHN',
                'codeNumerique' => '156',
            ],
            [
                'code' => '99230',
                'libelle' => 'Hong Kong',
                'libelleOfficiel' => 'Hong Kong',
                'alpha2' => 'HK',
                'alpha3' => 'HKG',
                'codeNumerique' => '344',
                'codeRattachement' => '99216',
            ],
            [
                'code' => '99217',
                'libelle' => 'Japon',
                'libelleOfficiel' => 'Japon',
                'alpha2' => 'JP',
                'alpha3' => 'JPN',
                'codeNumerique' => '392',
                'codeActualite' => '1',
            ],
            [
                'code' => '99224',
                'libelle' => 'Birmanie',
                'libelleOfficiel' => 'République de l’Union de Birmanie',
                'alpha2' => 'MM',
                'alpha3' => 'MMR',
                'codeNumerique' => '104',
                'anneeApparition' => '1948',
            ],
            [
                'code' => '99235',
                'libelle' => 'Sri Lanka',
                'libelleOfficiel' => 'République démocratique socialiste du Sri Lanka',
                'alpha2' => 'LK',
                'alpha3' => 'LKA',
                'codeNumerique' => '144',
            ],
            [
                'code' => '99302',
                'libelle' => 'Libéria',
                'libelleOfficiel' => 'République du Libéria',
                'alpha2' => 'LR',
                'alpha3' => 'LBR',
                'codeNumerique' => '430',
                'anneeApparition' => '1847',
            ],
            [
                'code' => '99401',
                'libelle' => 'Canada',
                'libelleOfficiel' => 'Canada',
                'alpha2' => 'CA',
                'alpha3' => 'CAN',
                'codeNumerique' => '124',
                'codeActualite' => '1',
            ],
            [
                'code' => '99101',
                'libelle' => 'Italie',
                'libelleOfficiel' => 'République italienne',
                'alpha2' => 'IT',
                'alpha3' => 'ITA',
                'codeNumerique' => '380',
            ],
        ];

        foreach ($pays as $row) {
            $entity = new InseePays();

            $entity->setCodePays($row['code']);
            $entity->setLibelleCog($row['libelle']);
            $entity->setLibelleOfficiel($row['libelleOfficiel']);
            $entity->setCodeIso2($row['alpha2']);
            $entity->setCodeIso3($row['alpha3']);
            $entity->setCodeIsoNum3($row['codeNumerique']);
            $entity->setImportId(self::IMPORT_ID);

            if (isset($row['anneeApparition'])) {
                $entity->setAnneeApparition($row['anneeApparition']);
            }

            if (isset($row['codeRattachement'])) {
                $entity->setCodeRattachement($row['codeRattachement']);
            }

            if (isset($row['codeActualite'])) {
                $entity->setCodeActualite($row['codeActualite']);
            }

            $manager->persist($entity);
        }

        $manager->flush();
    }
}
