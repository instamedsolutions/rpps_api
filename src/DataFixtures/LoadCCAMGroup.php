<?php

namespace App\DataFixtures;

use App\Entity\CCAMGroup;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadCCAMGroup extends Fixture implements FixtureInterface
{
    final public const string GROUP = 'ccam-group';

    final public const string CATEGORY = 'ccam-category';

    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $group = new CCAMGroup();

        $group->setCode('1');
        $group->setName('Système nerveux central, périphérique et autonome');
        $group->setDescription("À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.À l'exclusion de : analgésie postopératoirePar intrathécal, on entend : dans l'espace subarachnoïdien.Par infiltration anesthésique d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf, par voie transcutanée.Par bloc anesthésique continu d'un nerf, on entend : injection d'un agent pharmacologique au contact d'un nerf avec pose d'un cathéter, par voie transcutanée.");
        $group->setImportId('import_1');

        $this->em->persist($group);

        $group2 = new CCAMGroup();
        $group2->setCode('01.01');
        $group2->setName('Actes diagnostiques sur le système nerveux');
        $group2->setParent($group);
        $group2->setImportId('import_1');

        $this->em->persist($group2);

        $this->em->flush();

        $this->addReference(self::CATEGORY, $group);
        $this->addReference(self::GROUP, $group2);
    }
}
