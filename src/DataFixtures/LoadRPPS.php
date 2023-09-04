<?php

namespace App\DataFixtures;

use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadRPPS extends Fixture
{
    public string $importId = 'import_1';

    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $faker = Factory::create('fr_FR');

        $faker->seed(666);

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
                $rpps->setEmail(strtolower((string) "$user@instamed.fr"));
            }

            if (in_array($i, [0, 1, 4, 8])) {
                $rpps->setAddress($faker->streetAddress());
                $rpps->setCity($faker->city());
                $rpps->setZipcode($faker->postcode());
            }
            $rpps->setSpecialty($this->getSpecialties()[$i]);

            if (in_array($i, [0, 3, 5, 9])) {
                $rpps->setPhoneNumber($faker->phoneNumber());
            }

            $rpps->importId = $this->importId;

            $this->em->persist($rpps);
        }

        $this->em->flush();
    }

    protected function getUsers(): array
    {
        return ['Bastien', 'Jérémie', 'Luv', 'Julien', 'Lauriane', 'Maxime', 'Johann', 'Emilie', 'Blandine', 'Quentin'];
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
            ];

            return $ids[$j];
        }

        return "$first$j$j$j$j$j$j$j$j$j$j";
    }

    private function getSpecialties(): array
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
        ];
    }
}
