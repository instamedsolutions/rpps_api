<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\NGAP;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 *
 */
class LoadNGAP extends Fixture
{


    protected EntityManagerInterface $em;


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $entity = new NGAP();

        $entity->code = "C";
        $entity->description = "Consultation";

        $entity->importId = "import_1";
        $this->em->persist($entity);

        $entity2 = new NGAP();
        $entity2->code = "BDC";
        $entity2->description = "Examen bucco-dentaire";
        $entity2->importId = "import_1";

        $this->em->persist($entity2);

        $this->em->flush();
    }

}
