<?php

namespace App\DataFixtures;

use App\Entity\DiseaseGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadDiseaseGroups.
 */
class LoadDiseaseGroups extends Fixture implements FixtureInterface
{
    final public const GROUP = 'group';

    final public const CATEGORY = 'category';

    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $group = new DiseaseGroup();

        $group->setCim('01');
        $group->setName('Certaines maladies infectieuses et parasitaires');
        $group->setImportId('import_1');

        $this->em->persist($group);

        $group2 = new DiseaseGroup();
        $group2->setCim('A00-A09');
        $group2->setName('Maladies intestinales infectieuses');
        $group2->setImportId('import_1');

        $this->em->persist($group2);

        $this->em->flush();

        $this->addReference(self::CATEGORY, $group);
        $this->addReference(self::GROUP, $group2);
    }
}
