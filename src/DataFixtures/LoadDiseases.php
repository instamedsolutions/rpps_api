<?php

namespace App\DataFixtures;

use App\Entity\Disease;
use App\Entity\DiseaseGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 * Class LoadDiseases
 *
 * @package App\DataFixtures
 */
class LoadDiseases extends Fixture implements DependentFixtureInterface, FixtureInterface
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $disease = new Disease();
        $disease->setCim("A00");
        $disease->setGroup($this->getReference(LoadDiseaseGroups::GROUP));
        $disease->setCategory($this->getReference(LoadDiseaseGroups::CATEGORY));
        $disease->setName("Cholera");
        $disease->setHierarchyLevel(3);
        $disease->importId = "import_1";

        $this->em->persist($disease);

        $disease2 = new Disease();
        $disease2->setCim("A000");
        $disease2->setGroup($this->getReference(LoadDiseaseGroups::GROUP));
        $disease2->setCategory($this->getReference(LoadDiseaseGroups::CATEGORY));
        $disease2->setName("A Vibrio cholerae 01, biovar cholerae");
        $disease2->setHierarchyLevel(4);
        $disease2->setParent($disease);
        $disease2->setSex(Disease::SEX_FEMALE);
        $disease2->importId = "import_1";

        $this->em->persist($disease2);

        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [LoadDiseaseGroups::class];
    }


}
