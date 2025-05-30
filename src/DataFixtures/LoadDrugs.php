<?php

namespace App\DataFixtures;

use App\Entity\Drug;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadDrugs extends Fixture
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $drug = new Drug();
        $drug->setOwner('GLAXOSMITHKLINE SANTE GRAND PUBLIC');
        $drug->setName('ADVIL 200 mg, comprimé enrobé');
        $drug->setCisId('68634000');
        $drug->setPharmaceuticalForm('comprimé enrobé');
        $drug->setAdministrationForms(['orale']);
        $drug->setImportId('import_1');
        $this->em->persist($drug);

        $drug2 = new Drug();
        $drug2->setOwner('ACCORD HEALTHCARE FRANCE');
        $drug2->setName('PARACETAMOL 50,0 mg, comprimés effervescents');
        $drug2->setCisId('68634033');
        $drug2->setPresentationLabel('16 film(s) thermosoudé(s) papier polyéthylène aluminium
         P-A-M-éthylène (SURLYN) unitaires prédécoupés de 1 comprimé');
        $drug2->setPharmaceuticalForm('comprimé effervescent(e)');
        $drug2->setAdministrationForms(['orale']);
        $drug2->setSecurityText("<a target='_blank'  title=\"Lien direct vers l'information importante sur le site de l'ANSM - Nouvelle fenêtre\" href='https://www.ansm.sante.fr/S-informer/Points-d-information-Points-d-information/COVID-19-l-ANSM-prend-des-mesures-pour-favoriser-le-bon-usage-du-paracetamol'>COVID-19 : lANSM prend des mesures pour favoriser le bon usage du paracétamol</a>");
        $drug2->setImportId('import_1');
        $this->em->persist($drug2);

        $this->em->flush();
    }
}
