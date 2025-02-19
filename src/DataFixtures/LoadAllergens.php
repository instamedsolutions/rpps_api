<?php

namespace App\DataFixtures;

use App\Entity\Allergen;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadAllergens extends Fixture
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $entity = new Allergen();
        $entity->setCode('g1');
        $entity->setGroup('Pollens de graminées');
        $entity->setName('Flouve odorante');
        $entity->setImportId('import_1');
        $this->em->persist($entity);

        $entity2 = new Allergen();
        $entity2->setCode('c209');
        $entity2->setGroup('Médicaments');
        $entity2->setName('Chymopapaïne');
        $entity2->setTranslation('en', 'name', 'Chymopapain');
        $entity2->setTranslation('en', 'group', 'Drugs');
        $entity2->setImportId('import_1');
        $this->em->persist($entity2);

        $this->em->flush();
    }
}
