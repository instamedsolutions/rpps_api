<?php

namespace App\DataFixtures;

use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadRPPS extends Fixture
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    /**
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $faker = \Faker\Factory::create('fr_FR');

        $faker->seed(666);

        foreach ($this->getUsers() as $i => $user) {
            $j = $i+1;


            $rpps = new RPPS();
            $rpps->setFirstName($user);
            $rpps->setLastName("Test");
            if(in_array($i,array(0,3,4))) {
                $rpps->setTitle("Docteur");
            }
            $rpps->setIdRpps("{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");

            if(in_array($i,array(0,1,5))) {
                $rpps->setCpsNumber("{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");
            }

            if(in_array($i,array(0,2,3))) {
                $rpps->setFinessNumber("{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}{$j}");
            }
            if(in_array($i,array(0,4,5))) {
                $rpps->setEmail(strtolower("$user@instamed.fr"));
            }

            if(in_array($i,array(0,1,4))) {
                $rpps->setAddress($faker->streetAddress);
                $rpps->setCity($faker->city);
                $rpps->setZipcode($faker->postcode);
            }
            $rpps->setSpecialty($this->getSpecialties()[$i]);

            if(in_array($i,array(0,3,5))) {
                $rpps->setPhoneNumber($faker->phoneNumber);
            }

            $this->em->persist($rpps);
        }


        $this->em->flush();
    }


    /**
     * @return string[]
     */
    protected function getUsers() : array
    {
        return array("Bastien","Jérémie","Luv","Julien","Lauriane","Maxime");
    }


    /**
     * @return array
     */
    protected function getSpecialties()
    {
        return array('Qualifié en Médecine Générale','Sage-Femme','Masseur-Kinésithérapeute',null,'Pédiatrie','Pharmacien');
    }


}
