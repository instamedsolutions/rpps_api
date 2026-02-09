<?php

namespace App\Serializer\Normalizer;

use libphonenumber\PhoneNumber;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class PhoneNumberNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return "+{$data->getCountryCode()}{$data->getNationalNumber()}";
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof PhoneNumber;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            PhoneNumber::class => false,
        ];
    }
}
