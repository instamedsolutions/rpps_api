<?php

namespace App\Serializer\Normalizer;

use App\Entity\Cim11Modifier;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class Cim11ModifierNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const ALREADY_CALLED = 'CIM_11_MODIFIER';

    public function __construct(private readonly TranslatorInterface $translator)
    {
    }

    /**
     * @param Cim11Modifier $data
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $context[self::ALREADY_CALLED] = true;

        $data->setName($this->translator->trans("cim11_modifiers.{$data->getType()->name}", [], 'message'));

        return $this->normalizer->normalize($data, $format, $context);
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        if (!$data instanceof Cim11Modifier) {
            return false;
        }

        return true;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Cim11Modifier::class => false,
        ];
    }
}
