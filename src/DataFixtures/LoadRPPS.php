<?php

namespace App\DataFixtures;

use AllowDynamicProperties;
use App\Entity\RPPS;
use App\Entity\Specialty;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

#[AllowDynamicProperties]
class LoadRPPS extends Fixture implements DependentFixtureInterface
{
    public const string IMPORT_ID = 'import_1';
    public const string RPPS_USER_1 = '10101485653';
    public const string RPPS_USER_2 = '19900000002';

    protected ObjectManager $em;

    public function load(ObjectManager $manager): void
    {
        $this->em = $manager;

        $specialtyRepo = $this->em->getRepository(Specialty::class);
        $generalSpecialty = $specialtyRepo->findOneBy(['canonical' => 'medecine-generale']);
        $pediatricsSpecialty = $specialtyRepo->findOneBy(['canonical' => 'pediatrie']);
        $pharmacySpecialty = $specialtyRepo->findOneBy(['canonical' => 'pharmacien']);
        $infirmierSpecialty = $specialtyRepo->findOneBy(['canonical' => 'infirmier']);
        $sageFemmeSpecialty = $specialtyRepo->findOneBy(['canonical' => 'sage-femme']);

        $preloadedByCanonical = [
            'medecine-generale' => $generalSpecialty,
            'pediatrie' => $pediatricsSpecialty,
            'pharmacien' => $pharmacySpecialty,
            'infirmier' => $infirmierSpecialty,
            'sage-femme' => $sageFemmeSpecialty,
        ];

        foreach ($this->getUsers() as $user) {
            $rpps = new RPPS();

            // Champs de base
            $rpps->setIdRpps($user['idRpps']);
            $rpps->setFirstName($user['firstName']);
            $rpps->setLastName($user['lastName']);
            $rpps->setTitle($user['title']);
            $rpps->setEmail($user['email']);
            $rpps->setCpsNumber($user['cpsNumber']);
            $rpps->setFinessNumber($user['finessNumber']);
            $rpps->setPhoneNumber($user['phoneNumber']);
            $rpps->setCanonical($user['canonical']);
            $specKey = $user['specialty'] ?? null;
            if (is_string($specKey) && isset($preloadedByCanonical[$specKey])) {
                $rpps->setSpecialtyEntity($preloadedByCanonical[$specKey]);
            }
            $rpps->setImportId(self::IMPORT_ID);
            $this->em->persist($rpps);
        }

        $this->em->flush();
    }

    protected function getUsers(): array
    {
        return [
            [
                'idRpps' => self::RPPS_USER_1,
                'title' => 'Docteur',
                'lastName' => 'Ochrome',
                'firstName' => 'Mercure',
                'canonical' => 'fixture-canonical-0',
                'phoneNumber' => '+33123456789',
                'email' => 'mercure.ochrome@example.test',
                'finessNumber' => '750300667',
                'cpsNumber' => null,
                'specialty' => 'medecine-generale',
            ],
            [
                'idRpps' => self::RPPS_USER_2,
                'title' => 'Docteur',
                'lastName' => 'Bressan',
                'firstName' => 'Aurelien',
                'canonical' => 'fixture-canonical-1',
                'phoneNumber' => '+33400000000',
                'email' => 'aurelien.bressan@example.test',
                'finessNumber' => null,
                'cpsNumber' => null,
                'specialty' => 'medecine-generale',
            ],

            // Demo users (idRpps starts with "2" to match demo filter)
            [
                'idRpps' => '21234567890',
                'title' => 'Docteur',
                'lastName' => 'Demo',
                'firstName' => 'Emilie',
                'canonical' => 'fixture-canonical-demo-1',
                'phoneNumber' => '+33111111111',
                'email' => 'emilie.demo@example.test',
                'finessNumber' => null,
                'cpsNumber' => null,
                'specialty' => 'medecine-generale',
            ],
            [
                'idRpps' => '20987654321',
                'title' => 'Docteur',
                'lastName' => 'Demo',
                'firstName' => 'Jeremie',
                'canonical' => 'fixture-canonical-demo-2',
                'phoneNumber' => '+33122222222',
                'email' => 'jeremie.demo@example.test',
                'finessNumber' => null,
                'cpsNumber' => null,
                'specialty' => 'medecine-generale',
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [LoadSpecialty::class];
    }
}
