<?php

namespace App\DataFixtures;

use App\Entity\Cim11;
use App\Entity\Cim11Modifier;
use App\Entity\Cim11ModifierValue;
use App\Entity\ModifierType;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadDCim11 extends Fixture implements FixtureInterface
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $disease = new Cim11();
        $disease->setCode('2C6Y');
        $disease->setName('Autres tumeurs malignes du sein');
        $disease->setHierarchyLevel(2);
        $disease->setCim10Code('C50.9');
        $disease->setWhoId('1047754165/unspecified');
        $disease->setSynonyms([
            'tumeur maligne primitive du sein',
            'tumeur maligne du sein',
            'CA - [carcinome] du sein',
            'carcinome du sein SAI',
            'cancer du sein',
            'cancer mammaire',
            'cancer primitif du sein',
            'tumeur maligne du tissu conjonctif du sein',
        ]);

        $modifier = new Cim11Modifier();
        $modifier->setName('Manifestation');
        $modifier->setType(ModifierType::hasManifestation);
        $modifier->setMultiple(false);
        $disease->addModifier($modifier);
        $modifier->importId = 'import_1';

        $cim11Value2 = new Cim11ModifierValue();
        $cim11Value2->setName('Douleur cancéreuse chronique');
        $cim11Value2->setSynonyms([
            'foo',
        ]);
        $cim11Value2->setCode('MG30.10');
        $cim11Value2->setWhoId('322466810');
        $cim11Value2->importId = 'import_1';

        // ----
        $modifier1 = new Cim11Modifier();
        $modifier1->setName('Manifestation');
        $modifier1->setType(ModifierType::specificAnatomy);
        $modifier1->setMultiple(false);
        $disease->addModifier($modifier1);
        $modifier1->importId = 'import_1';

        $cim11Value1 = new Cim11ModifierValue();
        $cim11Value1->setName('Sein');
        $cim11Value1->setSynonyms([
            'foo',
        ]);
        $cim11Value1->setCode('XA12C1');
        $cim11Value1->setWhoId('831985561');
        $modifier1->addValue($cim11Value1);
        $cim11Value1->importId = 'import_1';

        // -----
        $modifier2 = new Cim11Modifier();
        $modifier2->setName('Latéralité');
        $modifier2->setType(ModifierType::laterality);
        $modifier2->setMultiple(false);
        $disease->addModifier($modifier2);
        $modifier2->importId = 'import_1';

        $cim11Value2 = new Cim11ModifierValue();
        $cim11Value2->setName('Bilatéral');
        $cim11Value2->setSynonyms([
            'foo',
        ]);
        $cim11Value2->setCode('XK9J');
        $cim11Value2->setWhoId('627678743');
        $modifier2->addValue($cim11Value2);
        $cim11Value2->importId = 'import_1';

        $cim11Value3 = new Cim11ModifierValue();
        $cim11Value3->setName('Gauche');
        $cim11Value3->setSynonyms([
            'foo',
        ]);
        $cim11Value3->setCode('XK8G');
        $cim11Value3->setWhoId('271422288');
        $modifier2->addValue($cim11Value3);
        $cim11Value3->importId = 'import_1';

        $cim11Value4 = new Cim11ModifierValue();
        $cim11Value4->setName('Droit');
        $cim11Value4->setSynonyms([
            'foo',
        ]);
        $cim11Value4->setCode('XK9K');
        $cim11Value4->setWhoId('876572005');
        $modifier2->addValue($cim11Value4);
        $cim11Value4->importId = 'import_1';

        $cim11Value5 = new Cim11ModifierValue();
        $cim11Value5->setName('Unilatéral');
        $cim11Value5->setSynonyms([
            'foo',
        ]);
        $cim11Value5->setCode('XK70');
        $cim11Value5->setWhoId('1038788978');
        $modifier2->addValue($cim11Value5);
        $cim11Value5->importId = 'import_1';

        $disease->importId = 'import_1';

        $this->em->persist($disease);
        $this->em->persist($modifier1);
        $this->em->persist($modifier2);
        $this->em->persist($cim11Value5);
        $this->em->persist($cim11Value1);
        $this->em->persist($cim11Value2);
        $this->em->persist($cim11Value3);
        $this->em->persist($cim11Value4);

        $disease2 = new Cim11();
        $disease2->setCode('CA00.0');
        $disease2->setName('Rhinopharyngite aigüe');
        $disease2->setWhoId('2066255370');
        $disease2->setHierarchyLevel(2);
        $disease2->setCim10Code('J00');
        $disease2->setSynonyms([
            'coryza aigu',
            'catarrhe nasal aigu',
            'rhinite aigüe',
            'rhinite infectieuse',
            'catarrhe nasopharyngé aigu',
            'rhinopharyngite infectieuse SAI',
            'mucosite SAI',
            'rhinopharyngite SAI',
            'épipharyngite',
            'maladie inflammatoire de la membrane muqueuse',
            'inflammation des muqueuses',
            'rhinite infectieuse aigüe',
            'coup de froid',
            'rhume banal',
            'coryza',
            'rhume',
            'rhinopharyngite infectieuse',
            'Rhinopharyngite',
        ]);

        $modifier2 = new Cim11Modifier();
        $modifier2->setName('Agent infectieux');
        $modifier2->setType(ModifierType::infectiousAgent);
        $modifier2->setMultiple(false);
        $disease2->addModifier($modifier2);
        $modifier2->importId = 'import_1';

        $disease2->importId = 'import_1';

        $this->em->persist($disease2);
        $this->em->persist($modifier2);

        $this->em->flush();
    }
}
