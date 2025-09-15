<?php

namespace App\Serializer\Normalizer;

use App\Entity\RPPS;
use App\Entity\RPPSAddress;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class RppsNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    private const string ALREADY_CALLED = 'rpps_normalizer_already_called';

    public function supportsNormalization($data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof RPPS;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }

    /**
     * @param RPPS $object
     *
     * @throws ExceptionInterface
     */
    public function normalize($object, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        // Normalisation de base par le normalizer par défaut
        $data = $this->normalizer->normalize($object, $format, $context);

        // Aplatis les champs legacy (adresse ET spécialité) pour rétrocompatibilité mobile.
        $this->legacyFlattenAddress($object, $data);
        $this->legacyFlattenSpecialty($object, $data);

        return $data;
    }

    /**
     * Aplatissement legacy de l'adresse au niveau RPPS.
     */
    private function legacyFlattenAddress(RPPS $object, array &$data): void
    {
        /** @var RPPSAddress|null $primary */
        $primary = $object->getAddresses()->first() ?: null;

        // Valeurs par défaut nulles
        $address = null;
        $addressExt = null;
        $zipcode = null;
        $cityName = null;
        $lat = null;
        $lng = null;
        $coords = ['latitude' => null, 'longitude' => null];

        if ($primary) {
            $address = $primary->getAddress();
            $addressExt = $primary->getAddressExtension();
            $zipcode = $primary->getZipcode();
            $cityName = $primary->getCityName();

            // Coordonnées de l'ADRESSE (pas de la ville)
            $lat = $primary->getLatitude();
            $lng = $primary->getLongitude();
            $coords = $primary->getCoordinates();
        }

        // Assigner systématiquement les clés legacy au niveau RPPS
        $data['address'] = $address;
        $data['addressExtension'] = $addressExt;
        $data['zipcode'] = $zipcode;
        $data['city'] = $cityName;
        $data['latitude'] = $lat;
        $data['longitude'] = $lng;
        $data['coordinates'] = $coords;
    }

    /**
     * Aplatissement legacy de la spécialité au niveau RPPS.
     */
    private function legacyFlattenSpecialty(RPPS $object, array &$data): void
    {
        $entity = $object->getSpecialtyEntity();
        $data['specialty'] = $entity?->getName();
    }
}
