<?php

namespace App\Serializer\Normalizer;

use ApiPlatform\Core\JsonApi\Serializer\ObjectNormalizer;
use App\Entity\Discussion;
use App\Entity\PatientProfile;
use App\Entity\PatientTreatment;
use App\Entity\Questionnaire\Question;
use App\Entity\Questionnaire\Questionnaire;
use App\Entity\User;
use App\Services\ProfileService;
use Doctrine\ORM\EntityManagerInterface;
use libphonenumber\PhoneNumber;
use \Symfony\Component\Security\Core\Security;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareNormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;

class PhoneNumberNormalizer implements ContextAwareNormalizerInterface, CacheableSupportsMethodInterface, NormalizerAwareInterface
{

    use NormalizerAwareTrait;


    private const ALREADY_CALLED = 'PHONE_NUMBER_NORMALIZER_ALREADY_CALLED';



    /**
     *
     * @param PhoneNumber $object
     * @param null $format
     * @param array $context
     * @return array
     * @throws \Symfony\Component\Serializer\Exception\ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = array()): string
    {

        $context[self::ALREADY_CALLED] = true;

        return "+{$object->getCountryCode()}{$object->getNationalNumber()}";

    }



    /**
     * @param mixed $data
     * @param null $format
     * @return bool
     */
    public function supportsNormalization($data, string $format = null,$context = array()): bool
    {

        if (isset($context[self::ALREADY_CALLED])) {
            return false;
        }

        return $data instanceof PhoneNumber;
    }


    /**
     * @return bool
     */
    public function hasCacheableSupportsMethod(): bool
    {
        return false;
    }
}
