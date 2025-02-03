<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\RPPS;
use App\Entity\Specialty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadRPPS extends Fixture implements DependentFixtureInterface, FixtureInterface
{
    public string $importId = 'import_1';

    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $faker = Factory::create('fr_FR');

        $faker->seed(666);

        $specialtyRepo = $this->em->getRepository(Specialty::class);
        $generalSpecialty = $specialtyRepo->findOneBy(['canonical' => 'medecine-generale']);
        $pediatricsSpecialty = $specialtyRepo->findOneBy(['canonical' => 'pediatrie']);
        $pharmacySpecialty = $specialtyRepo->findOneBy(['canonical' => 'pharmacien']);
        $infirmierSpecialty = $specialtyRepo->findOneBy(['canonical' => 'infirmier']);
        $sageFemmeSpecialty = $specialtyRepo->findOneBy(['canonical' => 'sage-femme']);

        foreach ($this->getUsers() as $i => $user) {
            $rpps = new RPPS();
            $rpps->setFirstName($user);
            $rpps->setLastName('Test');
            if (in_array($i, [0, 3, 4])) {
                $rpps->setTitle('Docteur');
            }

            $rppsId = $this->getRpps($i);

            $rpps->setIdRpps($rppsId);

            if (in_array($i, [0, 1, 5, 8])) {
                $rpps->setCpsNumber(substr($rppsId, 1, 10));
            }

            if (in_array($i, [0, 2, 3, 8])) {
                $rpps->setFinessNumber(substr($rppsId, 1, 9));
            }
            if (in_array($i, [0, 4, 5, 9])) {
                $rpps->setEmail(strtolower("$user@instamed.fr"));
            }

            if (in_array($i, [0, 1, 4, 8])) {
                $rpps->setAddress($faker->streetAddress());
                $rpps->setCity($faker->city());
                $rpps->setZipcode($faker->postcode());
                $rpps->setLatitude($faker->latitude());
                $rpps->setLongitude($faker->longitude());
            }
            $rpps->setSpecialty($this->getLegacySpecialties()[$i]);

            switch ($i) {
                case 0:
                case 1:
                    $rpps->setSpecialtyEntity($generalSpecialty);
                    break;
                case 2:
                case 3:
                    $rpps->setSpecialtyEntity($sageFemmeSpecialty);
                    break;
                case 4:
                case 5:
                case 6:
                case 7:
                    $rpps->setSpecialtyEntity($pediatricsSpecialty);
                    break;
                case 8:
                    $rpps->setSpecialtyEntity($pharmacySpecialty);
                    break;
                case 9:
                case 10:
                case 11:
                    $rpps->setSpecialtyEntity($infirmierSpecialty);
                    break;
            }

            if (in_array($i, [0, 3, 5, 9])) {
                $rpps->setPhoneNumber($faker->phoneNumber());
            }

            // Dynamically link cityEntity based on the INSEE code
            $cityInseeCode = $this->getCityInseeCode($i);
            $city = $this->em->getRepository(City::class)->findOneBy(['inseeCode' => $cityInseeCode]);

            if ($city) {
                $rpps->setCityEntity($city);
            }

            $rpps->setCanonical('fixture-canonical-' . $i);

            $rpps->setImportId($this->importId);

            $this->em->persist($rpps);
        }

        $this->em->flush();
    }

    protected function getUsers(): array
    {
        return [
            'Bastien',
            'Jérémie',
            'Luv',
            'Julien',
            'Lauriane',
            'Maxime',
            'Johann',
            'Emilie',
            'Blandine',
            'Quentin',
            'Achile',
        ];
    }

    private function getRpps(int $index): string
    {
        $j = $index + 1;

        $isDemo = $j > 6;

        $first = $isDemo ? 2 : 1;

        if ($j >= 10) {
            $ids = [
                10 => "{$first}1234567890",
                11 => "{$first}0987654321",
                12 => "{$first}4444455555",
            ];

            return $ids[$j];
        }

        return "$first$j$j$j$j$j$j$j$j$j$j";
    }

    private function getCityInseeCode(int $index): string
    {
        $cityInseeCodes = [
            '75104',
            '75104',
            '75105',
            '75105',
            '75120',
            '01050',
            '01050',
            '01050',
            '01053',
            '01053',
            '01053',
        ];

        return $cityInseeCodes[$index % count($cityInseeCodes)];
    }

    private function getLegacySpecialties(): array
    {
        return [
            'Qualifié en Médecine Générale',
            'Sage-Femme',
            'Masseur-Kinésithérapeute',
            null,
            'Pédiatrie',
            'Pharmacien',
            null,
            'Biologie médicale',
            'Radiologie',
            null,
            'Infirmier',
        ];
    }

    public function getDependencies(): array
    {
        return [LoadSpecialty::class, LoadCity::class];
    }
}
