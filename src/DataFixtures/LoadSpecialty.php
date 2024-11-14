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
            ['Allergologie', 'allergologie', 'Allergologue', 0],
            ['Anatomie Et Cytologie Pathologiques', 'anatomie-et-cytologie-pathologiques', 'Anatomopathologiste', 0],
            ['Anesthésie-Réanimation', 'anesthesie-reanimation', 'Anesthésiste-réanimateur', 0],
            ['Assistant Dentaire', 'assistant-dentaire', 'Assistant dentaire', 1],
            ['Assistant Social', 'assistant-social', 'Assistant social', 1],
            ['Audio-Prothésiste', 'audio-prothesiste', 'Audio-prothésiste', 1],
            ['Autre', 'autre', 'Professionnel de santé', 0],
            ['Biologie', 'biologie', 'Biologiste médical', 1],
            ['Cardiologie', 'cardiologie', 'Cardiologue', 0],
            ['Chiropracteur', 'chiropracteur', 'Chiropracteur', 1],
            ['Chirurgie Général', 'chirurgie-general', 'Chirurgien général', 0],
            ['Chirurgie Infantile', 'chirurgie-infantile', 'Chirurgien infantile', 0],
            ['Chirurgie Maxillo-Facial', 'chirurgie-maxillo-facial', 'Chirurgien maxillo-facial', 0],
            ['Chirurgie Orale', 'chirurgie-orale', 'Chirurgien oral', 0],
            ['Chirurgie Orthopédique', 'chirurgie-orthopedique', 'Chirurgien orthopédique', 0],
            ['Chirurgie Plastique', 'chirurgie-plastique', 'Chirurgien plasticien', 0],
            ['Chirurgie Pédiatrique', 'chirurgie-pediatrique', 'Chirurgien pédiatrique', 0],
            [
                'Chirurgie Thoracique Et Cardio-Vasculaire',
                'chirurgie-thoracique-et-cardio-vasculaire',
                'Chirurgien thoracique et cardio-vasculaire',
                0,
            ],
            ['Chirurgie Urologique', 'chirurgie-urologique', 'Chirurgien urologique', 0],
            ['Chirurgie Vasculaire', 'chirurgie-vasculaire', 'Chirurgien vasculaire', 0],
            ['Chirurgie Viscérale Et Digestive', 'chirurgie-viscerale-et-digestive', 'Chirurgien viscéral et digestif', 0],
            ['Chirurgien-Dentiste', 'chirurgien-dentiste', 'Chirurgien-dentiste', 0],
            ['Dentiste', 'dentiste', 'Chirurgien bucco-dentaire', 0],
            ['Dermatologie', 'dermatologie', 'Dermatologue', 0],
            ['Diététicien', 'dieteticien', 'Diététicien', 1],
            ['Endocrinologie', 'endocrinologie', 'Endocrinologue', 0],
            ['Epithésiste', 'epithesiste', 'Épithésiste', 0],
            ['Ergothérapeute', 'ergotherapeute', 'Ergothérapeute', 1],
            ['Gastro-Entérologie Et Hépatologie', 'gastro-enterologie-et-hepatologie', 'Gastro-entérologue', 0],
            ['Gynécologie', 'gynecologie', 'Gynécologue', 0],
            ['Génétique Médicale', 'genetique-medicale', 'Généticien', 0],
            ['Gériatrie', 'geriatrie', 'Gériatre', 0],
            ['Hématologie', 'hematologie', 'Hématologue', 0],
            ['Infirmier', 'infirmier', 'Infirmier', 1],
            ['Maladies Infectieuses Et Tropicales', 'maladies-infectieuses-et-tropicales', 'Infectiologue', 0],
            ['Manipulateur Erm', 'manipulateur-erm', 'Manipulateur en électroradiologie médicale', 0],
            ['Masseur-Kinésithérapeute', 'masseur-kinesitherapeute', 'Masseur-kinésithérapeute', 1],
            ['Médecine Cardiovasculaire', 'medecine-cardiovasculaire', 'Cardiologue interventionnel', 0],
            ['Médecine D\'Urgence', 'medecine-d-urgence', 'Urgentiste', 0],
            ['Médecine Du Travail', 'medecine-du-travail', 'Médecin du travail', 0],
            ['Médecine Générale', 'medecine-generale', 'Médecin généraliste', 0],
            ['Médecine Intensive-Réanimation', 'medecine-intensive-reanimation', 'Réanimateur', 0],
            ['Médecine Interne', 'medecine-interne', 'Interniste', 0],
            ['Médecine Légale Et Expertises Médicales', 'medecine-legale-et-expertises-medicales', 'Médecin légiste', 0],
            ['Médecine Nucléaire', 'medecine-nucleaire', 'Médecin nucléaire', 0],
            ['Médecine Physique Et Réadaptation', 'medecine-physique-et-readaptation', 'Rééducateur fonctionnel', 1],
            ['Médecine Vasculaire', 'medecine-vasculaire', 'Angiologue', 0],
            ['Neuro-Chirurgie', 'neuro-chirurgie', 'Neurochirurgien', 0],
            ['Neuro-Psychiatrie', 'neuro-psychiatrie', 'Neuropsychiatre', 0],
            ['Neurologie', 'neurologie', 'Neurologue', 0],
            ['Néphrologie', 'nephrologie', 'Néphrologue', 0],
            ['Obstétrique', 'obstetrique', 'Obstétricien', 0],
            ['Oculariste', 'oculariste', 'Oculariste', 0],
            ['Oncologie', 'oncologie', 'Oncologue', 0],
            ['Ophtalmologie', 'ophtalmologie', 'Ophtalmologue', 0],
            ['Opticien', 'opticien', 'Opticien', 1],
            ['Orl', 'orl', 'ORL', 0],
            ['Orthophoniste', 'orthophoniste', 'Orthophoniste', 1],
            ['Orthoprothésiste', 'orthoprothesiste', 'Orthoprothésiste', 1],
            ['Orthoptiste', 'orthoptiste', 'Orthoptiste', 1],
            ['Orthopédie', 'orthopedie', 'Orthopédiste', 1],
            ['Ostéopathe', 'osteopathe', 'Ostéopathe', 1],
            ['Pharmacien', 'pharmacien', 'Pharmacien', 1],
            ['Physicien Médical', 'physicien-medical', 'Physicien médical', 1],
            ['Pneumologie', 'pneumologie', 'Pneumologue', 0],
            ['Podo-Orthésiste', 'podo-orthesiste', 'Podo-orthésiste', 1],
            ['Psychiatrie', 'psychiatrie', 'Psychiatre', 0],
            ['Psychologue', 'psychologue', 'Psychologue', 1],
            ['Psychomotricien', 'psychomotricien', 'Psychomotricien', 0],
            ['Psychothérapeute', 'psychotherapeute', 'Psychothérapeute', 0],
            ['Pédiatrie', 'pediatrie', 'Pédiatre', 0],
            ['Pédicure-Podologue', 'pedicure-podologue', 'Pédicure-podologue', 0],
            ['Radio-Thérapie', 'radio-therapie', 'Radiothérapeute', 0],
            ['Radiologie', 'radiologie', 'Radiologue', 0],
            ['Recherche Médicale', 'recherche-medicale', 'Chercheur médical', 1],
            ['Rhumatologie', 'rhumatologie', 'Rhumatologue', 0],
            ['Sage-Femme', 'sage-femme', 'Sage-femme', 1],
            ['Santé Publique', 'sante-publique', 'Médecin de santé publique', 0],
            ['Stomatologie', 'stomatologie', 'Stomatologue', 0],
            ['Technicien De Laboratoire', 'technicien-de-laboratoire', 'Technicien de laboratoire', 1],
            ['Urologie', 'urologie', 'Urologue', 0],
        ];

        // Create and persist specialties
        foreach ($specialtiesData as $data) {
            $specialty = $this->em->getRepository(Specialty::class)->findOneBy([
                'canonical' => $data[1],
            ]);
            if (!$specialty) {
                $specialty = new Specialty();
                $specialty->setName($data[0]);
                $specialty->setCanonical($data[1]);
                $specialty->setSpecialistName($data[2]);
                $specialty->setIsParamedical((bool) $data[3]);
            }

            $this->em->persist($specialty);
        }

        $this->em->flush();
    }
}
