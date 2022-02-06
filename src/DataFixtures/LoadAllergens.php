<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 *
 */
class LoadAllergens extends Fixture
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

        $entity = new Allergen();
        $entity->setCode("g1");
        $entity->setGroup("Pollens de graminées");
        $entity->setName("Flouve odorante");
        $this->em->persist($entity);

        $entity2 = new Allergen();
        $entity2->setCode("c209");
        $entity2->setGroup("Médicaments");
        $entity2->setName("Chymopapaïne");
        $this->em->persist($entity2);

        $this->em->flush();

    }

}
