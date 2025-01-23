<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\Department;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCity extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        // Main cities data
        $citiesData = [
            ['Boissey', 'boissey', '01380', '01050', null, null, null, '01'],
            ['Bolozon', 'bolozon', '01450', '01051', '46.1960338503', '5.47095075411', '1190', '01'],
            ['Bouligneux', 'bouligneux', '01330', '01052', '46.0232774841', '4.99252709276', '334', '01'],
            ['Bourg-en-Bresse', 'bourg-en-bresse', '01000', '01053', '46.2051520382', '5.24602125501', '43306', '01'],
            ['Bourg-Saint-Christophe', 'bourg-st-christophe', '01800', '01054', '45.8861934456', '5.14349196896', '1395', '01'],
            ['Roissy-en-France', 'roissy-en-france', '95700', '95527', '49.006604440', '2.51417743519', '2908', '95'],
            ['Paris', 'paris', '75000', '75000', null, null, null, '75'],
        ];

        $ainDepartment = $this->em->getRepository(Department::class)->findOneBy(['codeDepartment' => '01']);
        $valDoiseDepartment = $this->em->getRepository(Department::class)->findOneBy(['codeDepartment' => '95']);
        $idfDepartment = $this->em->getRepository(Department::class)->findOneBy(['codeDepartment' => '75']);

        // Process main cities
        foreach ($citiesData as $data) {
            $city = new City();
            $city->setName($data[0]);
            $city->setCanonical($data[1]);
            $city->setPostalCode($data[2]);
            $city->setInseeCode($data[3]);
            $city->setLatitude($data[4]);
            $city->setLongitude($data[5]);
            $city->setPopulation((int) $data[6]);
            $city->setImportId('import_1');

            $department = match ($data[7]) {
                '01' => $ainDepartment,
                '95' => $valDoiseDepartment,
                '75' => $idfDepartment,
            };

            $city->setDepartment($department);

            $this->em->persist($city);
        }
        $this->em->flush();

        $subCityData = [
            // Paris arrondissements (1st to 20th as requested with 'Paris 01', 'Paris 02', ...)
            ['Paris 01', 'paris-1er', '75001', '75101', '48.862725', '2.287592', 100001],
            ['Paris 02', 'paris-2eme', '75002', '75102', '48.868957', '2.344488', 100002],
            ['Paris 03', 'paris-3eme', '75003', '75103', '48.863844', '2.360029', 100003],
            ['Paris 04', 'paris-4eme', '75004', '75104', '48.858844', '2.354264', 100004],
            ['Paris 05', 'paris-5eme', '75005', '75105', '48.844721', '2.347183', 100005],
            ['Paris 06', 'paris-6eme', '75006', '75106', '48.850958', '2.333985', 100006],
            ['Paris 07', 'paris-7eme', '75007', '75107', '48.858370', '2.294481', 100007],
            ['Paris 08', 'paris-8eme', '75008', '75108', '48.870637', '2.318747', 100008],
            ['Paris 09', 'paris-9eme', '75009', '75109', '48.876319', '2.343028', 100009],
            ['Paris 10', 'paris-10eme', '75010', '75110', '48.878319', '2.358083', 100010],
            ['Paris 11', 'paris-11eme', '75011', '75111', '48.857908', '2.378890', 100011],
            ['Paris 12', 'paris-12eme', '75012', '75112', '48.841402', '2.384743', 100012],
            ['Paris 13', 'paris-13eme', '75013', '75113', '48.832338', '2.355618', 100013],
            ['Paris 14', 'paris-14eme', '75014', '75114', '48.833675', '2.315948', 100014],
            ['Paris 15', 'paris-15eme', '75015', '75115', '48.841759', '2.292292', 100015],
            ['Paris 16', 'paris-16eme', '75016', '75116', '48.863776', '2.276995', 100016],
            ['Paris 17', 'paris-17eme', '75017', '75117', '48.886163', '2.309384', 100017],
            ['Paris 18', 'paris-18eme', '75018', '75118', '48.892401', '2.344324', 100018],
            ['Paris 19', 'paris-19eme', '75019', '75119', '48.889716', '2.375062', 100019],
            ['Paris 20', 'paris-20eme', '75020', '75120', '48.864173', '2.398300', 100020],
        ];

        // Find main city by its canonical and link it
        $paris = $this->em->getRepository(City::class)->findOneBy(['canonical' => 'paris']);

        // Process sub-cities and link them to the main cities
        foreach ($subCityData as $data) {
            $subCity = new City();
            $subCity->setMainCity($paris);
            $subCity->setDepartment($idfDepartment);
            $subCity->setName('Paris');
            $subCity->setSubCityName($data[0]);
            $subCity->setCanonical($data[1]);
            $subCity->setPostalCode($data[2]);
            $subCity->setInseeCode($data[3]);
            $subCity->setLatitude($data[4]);
            $subCity->setLongitude($data[5]);
            $subCity->setPopulation($data[6]);

            $subCity->setImportId('import_1');

            $this->em->persist($subCity);
        }

        // Set Bourg-en-Bresse as the chef-lieu of Ain department
        $bourgEnBresse = $this->em->getRepository(City::class)->findOneBy(['canonical' => 'bourg-en-bresse']);
        if ($bourgEnBresse && $ainDepartment) {
            $ainDepartment->setChefLieu($bourgEnBresse);
            $this->em->persist($ainDepartment);
        }

        $this->em->flush();
    }

    public function getDependencies(): array
    {
        return [LoadDepartment::class];
    }
}
