<?php

namespace App\DataFixtures;

use App\Entity\NGAP;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadNGAP extends Fixture
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $entity = new NGAP();

        $entity->code = 'C';
        $entity->description = 'Consultation';

        $entity->importId = 'import_1';
        $this->em->persist($entity);

        $entity2 = new NGAP();
        $entity2->code = 'BDC';
        $entity2->description = 'Examen bucco-dentaire';
        $entity2->importId = 'import_1';

        $this->em->persist($entity2);

        $this->em->flush();
    }
}
