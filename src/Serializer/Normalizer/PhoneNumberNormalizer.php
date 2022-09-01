<?php

namespace App\Serializer\Normalizer;

use libphonenumber\PhoneNumber;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PhoneNumberNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface,
                                       NormalizerAwareInterface
{

    use NormalizerAwareTrait;


    /**
     * @param $object
     * @param string|null $format
     */
    public function normalize($object, string $format = null, array $context = []): string
    {
        return "+{$object->getCountryCode()}{$object->getNationalNumber()}";
    }


    public function supportsNormalization($data, string $format = null, $context = []): bool
    {
        return $data instanceof PhoneNumber;
    }


    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }
}
