<?php

namespace App\DataFixtures;

use App\Entity\InseeCommune;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadInseeCommune extends Fixture
{
    private const string IMPORT_ID = 'import_1';

    public function load(ObjectManager $manager): void
    {
        $communes = [
            [
                'typeCommune' => 'COM',
                'codeCommune' => '75056',
                'codeRegion' => '11',
                'codeDepartement' => '75',
                'codeCollectivite' => '75C',
                'codeArrondissement' => '751',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'PARIS',
                'nomEnClairTypo' => 'Paris',
                'nomEnClairAvecArticle' => 'Paris',
                'codeCanton' => '7599',
            ],
            [
                'typeCommune' => 'ARM',
                'codeCommune' => '75101',
                'codeRegion' => '11',
                'codeDepartement' => '75',
                'codeCollectivite' => '75C',
                'codeArrondissement' => '751',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'PARIS 1ER ARRONDISSEMENT',
                'nomEnClairTypo' => 'Paris 1er Arrondissement',
                'nomEnClairAvecArticle' => 'Paris 1er Arrondissement',
                'codeCanton' => '7599',
                'codeCommuneParente' => '75056',
            ],
            [
                'typeCommune' => 'COM',
                'codeCommune' => '71343',
                'codeRegion' => '27',
                'codeDepartement' => '71',
                'codeCollectivite' => '71D',
                'codeArrondissement' => '712',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'PARIS L HOPITAL',
                'nomEnClairTypo' => "Paris-l'Hôpital",
                'nomEnClairAvecArticle' => "Paris-l'Hôpital",
                'codeCanton' => '7104',
            ],
            [
                'typeCommune' => 'COM',
                'codeCommune' => '81202',
                'codeRegion' => '76',
                'codeDepartement' => '81',
                'codeCollectivite' => '81D',
                'codeArrondissement' => '811',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'PARISOT',
                'nomEnClairTypo' => 'Parisot',
                'nomEnClairAvecArticle' => 'Parisot',
                'codeCanton' => '8110',
            ],
            [
                'typeCommune' => 'COM',
                'codeCommune' => '82137',
                'codeRegion' => '76',
                'codeDepartement' => '82',
                'codeCollectivite' => '82D',
                'codeArrondissement' => '822',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'PARISOT',
                'nomEnClairTypo' => 'Parisot',
                'nomEnClairAvecArticle' => 'Parisot',
                'codeCanton' => '8212',
            ],
            [
                'typeCommune' => 'COM',
                'codeCommune' => '95241',
                'codeRegion' => '11',
                'codeDepartement' => '95',
                'codeCollectivite' => '95D',
                'codeArrondissement' => '952',
                'typeNomEnClair' => '0',
                'nomEnClair' => 'FONTENAY EN PARISIS',
                'nomEnClairTypo' => 'Fontenay-en-Parisis',
                'nomEnClairAvecArticle' => 'Fontenay-en-Parisis',
                'codeCanton' => '9509',
            ],
            [
                'typeCommune' => 'COM',
                'codeCommune' => '02387',
                'codeRegion' => '32',
                'codeDepartement' => '02',
                'codeCollectivite' => '02D',
                'codeArrondissement' => '023',
                'typeNomEnClair' => '1',
                'nomEnClair' => 'ITANCOURT',
                'nomEnClairTypo' => 'Itancourt',
                'nomEnClairAvecArticle' => 'Itancourt',
                'codeCanton' => '0212',
            ],
        ];

        foreach ($communes as $row) {
            $entity = new InseeCommune();

            $entity->setTypeCommune($row['typeCommune']);
            $entity->setCodeCommune($row['codeCommune']);
            $entity->setCodeRegion($row['codeRegion']);
            $entity->setCodeDepartement($row['codeDepartement']);
            $entity->setCodeCollectivite($row['codeCollectivite']);
            $entity->setCodeArrondissement($row['codeArrondissement']);
            $entity->setTypeNomEnClair($row['typeNomEnClair']);
            $entity->setNomEnClair($row['nomEnClair']);
            $entity->setNomEnClairTypo($row['nomEnClairTypo']);
            $entity->setNomEnClairAvecArticle($row['nomEnClairAvecArticle']);
            $entity->setCodeCanton($row['codeCanton']);

            if (isset($row['codeCommuneParente'])) {
                $entity->setCodeCommuneParente($row['codeCommuneParente']);
            }

            $entity->setImportId(self::IMPORT_ID);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
