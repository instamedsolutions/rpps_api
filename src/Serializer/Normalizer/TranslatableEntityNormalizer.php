<?php

namespace App\Serializer\Normalizer;

use App\Entity\TranslatableEntityInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class TranslatableEntityNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const string ALREADY_CALLED = 'TRANSLATABLE_ENTITY_ALREADY_CALLED';

    public static $i = 0;

    /**
     * @param TranslatableEntityInterface $data
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $context[self::getAlreadyCalledId($data)] = true;

        self::$i++;

        if (!isset($context['languages'])) {
            return $this->normalizer->normalize($data, $format, $context);
        }

        // Get the default language from the object
        $defaultLanguage = $data->getDefaultLanguage();

        if ($context['languages'][0] === $defaultLanguage) {
            return $this->normalizer->normalize($data, $format, $context);
        }

        $translations = $data->getTranslationsForLangs($context['languages']);

        $oldValues = [];
        foreach ($translations as $key => $value) {
            // Build the getter method name dynamically
            $getter = 'get' . ucfirst($key);

            // Check if the method exists in the object
            if (method_exists($data, $getter)) {
                // Call the getter method dynamically
                $oldValues[$key] = $data->$getter();
            } else {
                // Handle the case where the getter does not exist
                $oldValues[$key] = null;
            }

            // Similarly, build and call the setter method
            $setter = 'set' . ucfirst($key);
            if (method_exists($data, $setter)) {
                if ('synonyms' === $key) {
                    $data->$setter([$value]);
                } else {
                    $data->$setter($value);
                }
            }
        }

        $result = $this->normalizer->normalize($data, $format, $context);

        // This is used to make sure we don't persist some bad values later on
        foreach ($oldValues as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($data, $setter)) {
                $data->$setter($value);
            }
        }

        return $result;
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {

        if (isset($context[self::getAlreadyCalledId($data)])) {
            return false;
        }

        // Only for API Platform
        if (!isset($context['groups'])) {
            return false;
        }

        return $data instanceof TranslatableEntityInterface;
    }

    private function getAlreadyCalledId(mixed $object): string
    {
        if (!$object instanceof TranslatableEntityInterface) {
            return self::ALREADY_CALLED;
        }
        $class = $object::class;

        return self::ALREADY_CALLED . $class . $object->getId();
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            TranslatableEntityInterface::class => false,
        ];
    }
}
