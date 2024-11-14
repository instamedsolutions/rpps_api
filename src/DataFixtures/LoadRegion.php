<?php

namespace App\DataFixtures;

use App\Entity\Region;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadRegion extends Fixture
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $regionsData = [
            ['Guadeloupe', '01'],
            ['Martinique', '02'],
            ['Guyane', '03'],
            ['La Réunion', '04'],
            ['Mayotte', '06'],
            ['Île-de-France', '11'],
            ['Centre-Val de Loire', '24'],
            ['Bourgogne-Franche-Comté', '27'],
            ['Normandie', '28'],
            ['Hauts-de-France', '32'],
            ['Grand Est', '44'],
            ['Pays de la Loire', '52'],
            ['Bretagne', '53'],
            ['Nouvelle-Aquitaine', '75'],
            ['Occitanie', '76'],
            ['Auvergne-Rhône-Alpes', '84'],
            ['Provence-Alpes-Côte d\'Azur', '93'],
            ['Corse', '94'],
            ['Saint-Pierre-et-Miquelon', '95'],
            ['Saint-Barthélémy', '96'],
            ['Saint-Martin', '97'],
            ['Polynésie française', '98'],
            ['Île de Clipperton', '99'],
            ['Wallis-et-Futuna', '100'],
            ['Nouvelle-Calédonie', '101'],
            ['Terres australes et antarctiques françaises', '102'],
        ];

        foreach ($regionsData as [$name, $codeRegion]) {
            $region = $this->em->getRepository(Region::class)->findOneBy([
                'codeRegion' => $codeRegion,
            ]);
            if (!$region) {
                $region = new Region();
                $region->setName($name);
                $region->setCodeRegion($codeRegion);
            }
            $region->importId = 'import_1';

            $manager->persist($region);
        }

        $manager->flush();
    }
}
