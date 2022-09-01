<?php

namespace App\DataFixtures;

use App\Entity\CCAM;
use App\Entity\Disease;
use App\Entity\DiseaseGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

/**
 *
 */
class LoadCCAM extends Fixture implements DependentFixtureInterface, FixtureInterface
{

    /**
     * @var EntityManagerInterface
     */
    protected $em;


    public function load(ObjectManager $manager)
    {
        $this->em = $manager;

        $ccam = new CCAM();
        $ccam->setCode("AHQP001");
        $ccam->setName("Électromyographie par électrode de surface, sans enregistrement vidéo");
        $ccam->setGroup($this->getReference(LoadCCAMGroup::GROUP));
        $ccam->setCategory($this->getReference(LoadCCAMGroup::CATEGORY));
        $ccam->setRegroupementCode("ATM");

        $this->em->persist($ccam);

        $ccam2 = new CCAM();
        $ccam2->setCode("AHQB026");
        $ccam2->setName(
            "Pyrographie de 3 à 6 muscles striés au repos et à l'effort avec stimulodétection, par électrode aiguille"
        );
        $ccam2->setDescription(
            "Formation : spécifique à cet acte en plus de la formation initialeFormation : spécifique à cet acte en plus de la formation initiale"
        );
        $ccam2->setGroup($this->getReference(LoadCCAMGroup::GROUP));
        $ccam2->setCategory($this->getReference(LoadCCAMGroup::CATEGORY));
        $ccam2->setModifiers(["F", "P", "S", "U"]);
        $ccam2->setRate1(86.4);
        $ccam2->setRate2(86.4);
        $ccam2->setRegroupementCode("ATM");

        $this->em->persist($ccam2);

        $this->em->flush();
    }

    /**
     * @return string[]
     */
    public function getDependencies(): array
    {
        return [LoadCCAMGroup::class];
    }


}
