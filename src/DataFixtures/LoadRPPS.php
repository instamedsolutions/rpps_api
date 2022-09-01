<?php

namespace App\DataFixtures;

use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class LoadRPPS extends Fixture
{


    public string $importId = "import_1";

    protected EntityManagerInterface $em;


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $faker = Factory::create('fr_FR');

        $faker->seed(666);

        foreach ($this->getUsers() as $i => $user) {
            $j = $i + 1;

            $isDemo = $j > 6;

            $rpps = new RPPS();
            $rpps->setFirstName($user);
            $rpps->setLastName("Test");
            if (in_array($i, [0, 3, 4])) {
                $rpps->setTitle("Docteur");
            }

            $first = $isDemo ? 2 : 1;

            $rpps->setIdRpps("$first{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");

            if (in_array($i, [0, 1, 5])) {
                $rpps->setCpsNumber("{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");
            }

            if (in_array($i, [0, 2, 3])) {
                $rpps->setFinessNumber("{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");
            }
            if (in_array($i, [0, 4, 5])) {
                $rpps->setEmail(strtolower((string)"$user@instamed.fr"));
            }

            if (in_array($i, [0, 1, 4])) {
                $rpps->setAddress($faker->streetAddress());
                $rpps->setCity($faker->city());
                $rpps->setZipcode($faker->postcode());
            }
            $rpps->setSpecialty($this->getSpecialties()[$i]);

            if (in_array($i, [0, 3, 5])) {
                $rpps->setPhoneNumber($faker->phoneNumber());
            }

            $rpps->importId = $this->importId;

            $this->em->persist($rpps);
        }


        $this->em->flush();
    }



    protected function getUsers(): array
    {
        return ["Bastien", "Jérémie", "Luv", "Julien", "Lauriane", "Maxime", "Johann", "Emilie"];
    }


    protected function getSpecialties(): array
    {
        return [
            'Qualifié en Médecine Générale',
            'Sage-Femme',
            'Masseur-Kinésithérapeute',
            null,
            'Pédiatrie',
            'Pharmacien',
            null,
            'Biologie médicale'
        ];
    }


}
