<?php

namespace App\DataFixtures;

use App\Entity\DiseaseGroup;
use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadDiseaseGroups
 *
 * @package App\DataFixtures
 */
class LoadDiseaseGroups extends Fixture implements FixtureInterface
{


    final const GROUP = 'group';

    final const CATEGORY = 'category';


    /**
     * @var EntityManagerInterface
     */
    protected $em;


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $group = new DiseaseGroup();

        $group->setCim("01");
        $group->setName("Certaines maladies infectieuses et parasitaires");
        $group->importId = "import_1";

        $this->em->persist($group);

        $group2 = new DiseaseGroup();
        $group2->setCim("A00-A09");
        $group2->setName("Maladies intestinales infectieuses");
        $group2->importId = "import_1";

        $this->em->persist($group2);

        $this->em->flush();

        $this->addReference(self::CATEGORY, $group);
        $this->addReference(self::GROUP, $group2);
    }

}
