<?php

namespace App\DataFixtures;

use App\Entity\Drug;
use App\Entity\RPPS;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDrugs extends Fixture
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

        $drug = new Drug();
        $drug->setOwner("GLAXOSMITHKLINE SANTE GRAND PUBLIC");
        $drug->setName(" ADVIL 200 mg ");
        $drug->setCisId("68634000");
        $drug->setPharmaceuticalForm("comprimé enrobé");
        $drug->setAdministrationForms(array("orale"));
        $this->em->persist($drug);

        $drug2 = new Drug();
        $drug2->setOwner("ACCORD HEALTHCARE FRANCE");
        $drug2->setName("PARACETAMOL 50,0 mg, comprimés effervescents");
        $drug2->setCisId("68634033");
        $drug2->setPresentationLabel("16 film(s) thermosoudé(s) papier polyéthylène aluminium P-A-M-éthylène (SURLYN) unitaires prédécoupés de 1 comprimé");
        $drug2->setPharmaceuticalForm("comprimé effervescent(e)");
        $drug2->setAdministrationForms(array("orale"));
        $drug2->setSecurityText("<a target='_blank'  title=\"Lien direct vers l'information importante sur le site de l'ANSM - Nouvelle fenêtre\" href='https://www.ansm.sante.fr/S-informer/Points-d-information-Points-d-information/COVID-19-l-ANSM-prend-des-mesures-pour-favoriser-le-bon-usage-du-paracetamol'>COVID-19 : lANSM prend des mesures pour favoriser le bon usage du paracétamol</a>");
        $this->em->persist($drug2);

        $this->em->flush();

    }

}
