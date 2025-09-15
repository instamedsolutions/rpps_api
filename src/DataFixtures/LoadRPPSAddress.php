<?php

namespace App\DataFixtures;

use App\Entity\City;
use App\Entity\RPPS;
use App\Entity\RPPSAddress;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadRPPSAddress extends Fixture implements DependentFixtureInterface
{
    private function getAddresses(): array
    {
        return [
            LoadRPPS::RPPS_USER_1 => [
                [
                    'address' => '10 Rue de la Paix',
                    'addressExtension' => 'Bât A',
                    'zipcode' => '75002',
                    'cityCanonical' => 'paris-2eme',
                    'latitude' => 48.8686,
                    'longitude' => 2.3314,
                ],
                [
                    'address' => '25 Avenue des Champs',
                    'addressExtension' => 'Bât B',
                    'zipcode' => '75008',
                    'cityCanonical' => 'paris-8eme',
                    'latitude' => 48.870637,
                    'longitude' => 2.318747,
                ],
            ],
            LoadRPPS::RPPS_USER_2 => [
                [
                    'address' => '3 Place de la Préfecture',
                    'addressExtension' => 'Etage 2',
                    'zipcode' => '01000',
                    'cityCanonical' => 'bourg-en-bresse',
                    'latitude' => 46.2052,
                    'longitude' => 5.2460,
                ],
            ],
        ];
    }

    public function load(ObjectManager $manager): void
    {
        $rppsRepo = $manager->getRepository(RPPS::class);
        $cityRepo = $manager->getRepository(City::class);

        foreach ($this->getAddresses() as $idRpps => $addresses) {
            /** @var RPPS|null $rpps */
            $rpps = $rppsRepo->findOneBy(['idRpps' => $idRpps]);
            if (!$rpps) {
                // RPPS manquant: on saute silencieusement
                continue;
            }

            foreach ($addresses as $a) {
                $address = $a['address'] ?? null;
                $addressExt = $a['addressExtension'] ?? null;
                $zipcode = $a['zipcode'] ?? null;

                $rppsAddress = new RPPSAddress();
                $rpps->addAddress($rppsAddress);
                $rppsAddress->setAddress($address);
                $rppsAddress->setAddressExtension($addressExt);
                $rppsAddress->setZipcode($zipcode);

                if (array_key_exists('cityCanonical', $a)) {
                    $cityEntity = $cityRepo->findOneBy(['canonical' => $a['cityCanonical']]);
                    if ($cityEntity) {
                        $rppsAddress->setCity($cityEntity);
                    }
                }

                if (array_key_exists('latitude', $a)) {
                    $rppsAddress->setLatitude($a['latitude']);
                }
                if (array_key_exists('longitude', $a)) {
                    $rppsAddress->setLongitude($a['longitude']);
                }

                $rppsAddress->syncCoordinatesFromLatLong();

                // Compute the originalAddress from current fields for consistency
                $rppsAddress->refreshOriginalAddress();

                $rppsAddress->setMd5AddressFromParts($address, $rppsAddress->getCity(), $zipcode);
                $rppsAddress->setImportId(LoadRPPS::IMPORT_ID);

                $manager->persist($rpps);
                $manager->persist($rppsAddress);
            }
        }

        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [LoadCity::class, LoadRPPS::class];
    }
}
