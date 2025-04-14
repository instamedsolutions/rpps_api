<?php

namespace App\DataFixtures;

use App\Entity\InseeCommune1943;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadInseeCommune1943 extends Fixture
{
    private const string IMPORT_ID = 'import_1';

    public function load(ObjectManager $manager): void
    {
        $data = [
            [
                'code' => '01001',
                'tncc' => '5',
                'maj' => 'ABERGEMENT CLEMENCIAT',
                'typo' => 'Abergement-Clémenciat',
                'article' => "L'Abergement-Clémenciat",
                'debut' => '1943-01-01',
                'fin' => null,
            ],
            [
                'code' => '01002',
                'tncc' => '5',
                'maj' => 'ABERGEMENT DE VAREY',
                'typo' => 'Abergement-de-Varey',
                'article' => "L'Abergement-de-Varey",
                'debut' => '1943-01-01',
                'fin' => null,
            ],
            [
                'code' => '01003',
                'tncc' => '1',
                'maj' => 'AMAREINS',
                'typo' => 'Amareins',
                'article' => 'Amareins',
                'debut' => '1943-01-01',
                'fin' => '1974-01-01',
            ],
            [
                'code' => '01004',
                'tncc' => '1',
                'maj' => 'AMBERIEU',
                'typo' => 'Ambérieu',
                'article' => 'Ambérieu',
                'debut' => '1943-01-01',
                'fin' => '1955-03-31',
            ],
            [
                'code' => '01004',
                'tncc' => '1',
                'maj' => 'AMBERIEU EN BUGEY',
                'typo' => 'Ambérieu-en-Bugey',
                'article' => 'Ambérieu-en-Bugey',
                'debut' => '1955-03-31',
                'fin' => null,
            ],
            [
                'code' => '01021',
                'tncc' => '1',
                'maj' => 'ARS',
                'typo' => 'Ars',
                'article' => 'Ars',
                'debut' => '1943-01-01',
                'fin' => '1956-10-19',
            ],
            [
                'code' => '01021',
                'tncc' => '1',
                'maj' => 'ARS SUR FORMANS',
                'typo' => 'Ars-sur-Formans',
                'article' => 'Ars-sur-Formans',
                'debut' => '1956-10-19',
                'fin' => null,
            ],
        ];

        foreach ($data as $row) {
            $entity = new InseeCommune1943();

            $entity->setCodeCommune($row['code']);
            $entity->setTypeNomEnClair($row['tncc']);
            $entity->setNomMajuscule($row['maj']);
            $entity->setNomTypographie($row['typo']);
            $entity->setNomAvecArticle($row['article']);
            $entity->setDateDebut(new DateTime($row['debut']));

            if ($row['fin']) {
                $entity->setDateFin(new DateTime($row['fin']));
            }

            $entity->setImportId(self::IMPORT_ID);
            $manager->persist($entity);
        }

        $manager->flush();
    }
}
