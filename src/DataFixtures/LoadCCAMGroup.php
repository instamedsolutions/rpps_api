<?php

namespace App\DataFixtures;

use App\Entity\CCAMGroup;
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
class LoadCCAMGroup extends Fixture implements FixtureInterface
{


    const GROUP = 'ccam-group';

    const CATEGORY = 'ccam-category';



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

        $group = new CCAMGroup();

        $group->setCode("1");
        $group->setName("Système nerveux central, périphérique et autonome");
        $group->setDescription("À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.");

        $this->em->persist($group);

        $group2 = new CCAMGroup();
        $group2->setCode("01.01");
        $group2->setName("Actes diagnostiques sur le système nerveux");
        $group2->setParent($group);

        $this->em->persist($group2);

        $this->em->flush();

        $this->addReference(self::CATEGORY,$group);
        $this->addReference(self::GROUP,$group2);
    }

}
