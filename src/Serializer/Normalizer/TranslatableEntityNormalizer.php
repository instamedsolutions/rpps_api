<?php

namespace App\Serializer\Normalizer;

use App\Entity\TranslatableEntityInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class TranslatableEntityNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    public const string ALREADY_CALLED = 'TRANSLATABLE_ENTITY_ALREADY_CALLED';

    /**
     * @param TranslatableEntityInterface $object
     *
     * @throws ExceptionInterface
     */
    public function normalize(mixed $object, ?string $format = null, array $context = []): array
    {
        $context[self::getAlreadyCalledId($object)] = true;

        if (!isset($context['languages'])) {
            return $this->normalizer->normalize($object, $format, $context);
        }

        // Get the default language from the object
        $defaultLanguage = $object->getDefaultLanguage();

        if ($context['languages'][0] === $defaultLanguage) {
            return $this->normalizer->normalize($object, $format, $context);
        }

        $translations = $object->getTranslationsForLangs($context['languages']);

        $oldValues = [];
        foreach ($translations as $key => $value) {
            // Build the getter method name dynamically
            $getter = 'get' . ucfirst($key);

            // Check if the method exists in the object
            if (method_exists($object, $getter)) {
                // Call the getter method dynamically
                $oldValues[$key] = $object->$getter();
            } else {
                // Handle the case where the getter does not exist
                $oldValues[$key] = null;
            }

            // Similarly, build and call the setter method
            $setter = 'set' . ucfirst($key);
            if (method_exists($object, $setter)) {

                // TODO here can we detect if the setter wants an array or string ?

                if ('synonyms' === $key) {
                    $object->$setter([$value]);
                } else {
                    $object->$setter($value);
                }
            }
        }

        $data = $this->normalizer->normalize($object, $format, $context);

        // This is used to make sure we don't persist some bad values later on
        foreach ($oldValues as $key => $value) {
            $setter = 'set' . ucfirst($key);
            if (method_exists($object, $setter)) {
                $object->$setter($value);
            }
        }

        return $data;
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
        if (!($object instanceof TranslatableEntityInterface)) {
            return self::ALREADY_CALLED;
        }
        $class = $object::class;

        return self::ALREADY_CALLED . $class . $object->getId();
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
