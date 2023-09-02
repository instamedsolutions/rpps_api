<?php

declare(strict_types=1);

namespace App\Swagger;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class SwaggerDecorator implements NormalizerInterface
{
    public function __construct(private readonly NormalizerInterface $decorated)
    {
    }

    /**
     * @param mixed $data
     * @param null  $format
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }

    public function normalize($object, string $format = null, array $context = []): array
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        return $this->updateDoc($docs);
    }

    public function updateDoc(array $docs): array
    {
        $docs['definitions']['PhoneNumber'] = [
            'type' => 'string',
            'description' => 'The phone number with country code',
            'example' => '+33654955566',
        ];

        return $docs;
    }
}
