<?php

namespace App\DataFixtures;

use App\Entity\Specialty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadSpecialty extends Fixture
{
    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        // Array of all 81 specialties
        $specialtiesData = [
            ['Allergologie', 'allergologie', 'Allergologue'],
            ['Anatomie Et Cytologie Pathologiques', 'anatomie-et-cytologie-pathologiques', 'Anatomopathologiste'],
            ['Anesthésie-Réanimation', 'anesthesie-reanimation', 'Anesthésiste-réanimateur'],
            ['Assistant Dentaire', 'assistant-dentaire', 'Assistant dentaire'],
            ['Assistant Social', 'assistant-social', 'Assistant social'],
            ['Audio-Prothésiste', 'audio-prothesiste', 'Audio-prothésiste'],
            ['Autre', 'autre', 'Professionnel de santé'],
            ['Biologie', 'biologie', 'Biologiste médical'],
            ['Cardiologie', 'cardiologie', 'Cardiologue'],
            ['Chiropracteur', 'chiropracteur', 'Chiropracteur'],
            ['Chirurgie Général', 'chirurgie-general', 'Chirurgien général'],
            ['Chirurgie Infantile', 'chirurgie-infantile', 'Chirurgien infantile'],
            ['Chirurgie Maxillo-Facial', 'chirurgie-maxillo-facial', 'Chirurgien maxillo-facial'],
            ['Chirurgie Orale', 'chirurgie-orale', 'Chirurgien oral'],
            ['Chirurgie Orthopédique', 'chirurgie-orthopedique', 'Chirurgien orthopédique'],
            ['Chirurgie Plastique', 'chirurgie-plastique', 'Chirurgien plasticien'],
            ['Chirurgie Pédiatrique', 'chirurgie-pediatrique', 'Chirurgien pédiatrique'],
            [
                'Chirurgie Thoracique Et Cardio-Vasculaire',
                'chirurgie-thoracique-et-cardio-vasculaire',
                'Chirurgien thoracique et cardio-vasculaire'
            ],
            ['Chirurgie Urologique', 'chirurgie-urologique', 'Chirurgien urologique'],
            ['Chirurgie Vasculaire', 'chirurgie-vasculaire', 'Chirurgien vasculaire'],
            ['Chirurgie Viscérale Et Digestive', 'chirurgie-viscerale-et-digestive', 'Chirurgien viscéral et digestif'],
            ['Chirurgien-Dentiste', 'chirurgien-dentiste', 'Chirurgien-dentiste'],
            ['Dentiste', 'dentiste', 'Chirurgien bucco-dentaire'],
            ['Dermatologie', 'dermatologie', 'Dermatologue'],
            ['Diététicien', 'dieteticien', 'Diététicien'],
            ['Endocrinologie', 'endocrinologie', 'Endocrinologue'],
            ['Epithésiste', 'epithesiste', 'Épithésiste'],
            ['Ergothérapeute', 'ergotherapeute', 'Ergothérapeute'],
            ['Gastro-Entérologie Et Hépatologie', 'gastro-enterologie-et-hepatologie', 'Gastro-entérologue'],
            ['Gynécologie', 'gynecologie', 'Gynécologue médical'],
            ['Génétique Médicale', 'genetique-medicale', 'Généticien médical'],
            ['Gériatrie', 'geriatrie', 'Gériatre'],
            ['Hématologie', 'hematologie', 'Hématologue'],
            ['Infirmier', 'infirmier', 'Infirmier en pratique avancée'],
            ['Maladies Infectieuses Et Tropicales', 'maladies-infectieuses-et-tropicales', 'Infectiologue'],
            ['Manipulateur Erm', 'manipulateur-erm', 'Manipulateur en électroradiologie médicale'],
            ['Masseur-Kinésithérapeute', 'masseur-kinesitherapeute', 'Masseur-kinésithérapeute'],
            ['Médecine Cardiovasculaire', 'medecine-cardiovasculaire', 'Cardiologue interventionnel'],
            ['Médecine D\'Urgence', 'medecine-d-urgence', 'Urgentiste'],
            ['Médecine Du Travail', 'medecine-du-travail', 'Médecin du travail'],
            ['Médecine Générale', 'medecine-generale', 'Médecin généraliste'],
            ['Médecine Intensive-Réanimation', 'medecine-intensive-reanimation', 'Réanimateur médical'],
            ['Médecine Interne', 'medecine-interne', 'Interniste'],
            ['Médecine Légale Et Expertises Médicales', 'medecine-legale-et-expertises-medicales', 'Médecin légiste'],
            ['Médecine Nucléaire', 'medecine-nucleaire', 'Médecin nucléaire'],
            ['Médecine Physique Et Réadaptation', 'medecine-physique-et-readaptation', 'Rééducateur fonctionnel'],
            ['Médecine Vasculaire', 'medecine-vasculaire', 'Angiologue'],
            ['Neuro-Chirurgie', 'neuro-chirurgie', 'Neurochirurgien'],
            ['Neuro-Psychiatrie', 'neuro-psychiatrie', 'Neuropsychiatre'],
            ['Neurologie', 'neurologie', 'Neurologue'],
            ['Néphrologie', 'nephrologie', 'Néphrologue'],
            ['Obstétrique', 'obstetrique', 'Obstétricien'],
            ['Oculariste', 'oculariste', 'Oculariste'],
            ['Oncologie', 'oncologie', 'Oncologue'],
            ['Ophtalmologie', 'ophtalmologie', 'Ophtalmologue'],
            ['Opticien', 'opticien', 'Opticien'],
            ['Orl', 'orl', 'ORL'],
            ['Orthophoniste', 'orthophoniste', 'Orthophoniste'],
            ['Orthoprothésiste', 'orthoprothesiste', 'Orthoprothésiste'],
            ['Orthoptiste', 'orthoptiste', 'Orthoptiste'],
            ['Orthopédie', 'orthopedie', 'Orthopédiste'],
            ['Ostéopathe', 'osteopathe', 'Ostéopathe'],
            ['Pharmacien', 'pharmacien', 'Pharmacien'],
            ['Physicien Médical', 'physicien-medical', 'Physicien médical'],
            ['Pneumologie', 'pneumologie', 'Pneumologue'],
            ['Podo-Orthésiste', 'podo-orthesiste', 'Podo-orthésiste'],
            ['Psychiatrie', 'psychiatrie', 'Psychiatre'],
            ['Psychologue', 'psychologue', 'Psychologue'],
            ['Psychomotricien', 'psychomotricien', 'Psychomotricien'],
            ['Psychothérapeute', 'psychotherapeute', 'Psychothérapeute'],
            ['Pédiatrie', 'pediatrie', 'Pédiatre'],
            ['Pédicure-Podologue', 'pedicure-podologue', 'Pédicure-podologue'],
            ['Radio-Thérapie', 'radio-therapie', 'Radiothérapeute'],
            ['Radiologie', 'radiologie', 'Radiologue'],
            ['Recherche Médicale', 'recherche-medicale', 'Chercheur médical'],
            ['Rhumatologie', 'rhumatologie', 'Rhumatologue'],
            ['Sage-Femme', 'sage-femme', 'Sage-femme'],
            ['Santé Publique', 'sante-publique', 'Médecin de santé publique'],
            ['Stomatologie', 'stomatologie', 'Stomatologue'],
            ['Technicien De Laboratoire', 'technicien-de-laboratoire', 'Technicien de laboratoire'],
            ['Urologie', 'urologie', 'Urologue']
        ];

        // Create and persist specialties
        foreach ($specialtiesData as $data) {
            $specialty = new Specialty();
            $specialty->setName($data[0]);
            $specialty->setCanonical($data[1]);
            $specialty->setSpecialistName($data[2]);
            $specialty->importId = 'import_1';

            $this->em->persist($specialty);
        }

        $this->em->flush();

        // Fetch the 4 specialties that will be linked to all others
        $allergologie = $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => 'allergologie']);
        $anatomie = $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => 'anatomie-et-cytologie-pathologiques']);
        $generale = $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => 'medecine-generale']);
        $stomatologie = $this->em->getRepository(Specialty::class)->findOneBy(['canonical' => 'stomatologie']);

        // Add relationships between specialties (same 3 specialties for simplicity)
        foreach ($this->em->getRepository(Specialty::class)->findAll() as $specialty) {
            if ($specialty !== $allergologie) {
                $specialty->addSpecialty($allergologie);
            } else {
                $specialty->addSpecialty($stomatologie);
            }

            if ($specialty !== $anatomie) {
                $specialty->addSpecialty($anatomie);
            } else {
                $specialty->addSpecialty($stomatologie);
            }

            if ($specialty !== $generale) {
                $specialty->addSpecialty($generale);
            } else {
                $specialty->addSpecialty($stomatologie);
            }

            $this->em->persist($specialty);
        }

        $this->em->flush();
    }
}
