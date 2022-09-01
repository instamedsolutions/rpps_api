<?php

declare(strict_types=1);

namespace App\Swagger;

use ArrayObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\PropertyInfo\Extractor\SerializerExtractor;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;

final class SwaggerDecorator implements NormalizerInterface
{
    protected $serializerExtractor;

    /**
     * SwaggerDecorator constructor.
     */
    public function __construct(private readonly NormalizerInterface $decorated)
    {
        $serializerClassMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader));
        $serializerExtractor = new SerializerExtractor($serializerClassMetadataFactory);

        $this->serializerExtractor = $serializerExtractor;
    }


    /**
     * @param mixed $data
     * @param null $format
     */
    public function supportsNormalization($data, string $format = null): bool
    {
        return $this->decorated->supportsNormalization($data, $format);
    }


    /**
     * @param mixed $object
     * @param null $format
     * @return array|ArrayObject|bool|float|int|string|null
     * @throws ExceptionInterface
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        $docs = $this->decorated->normalize($object, $format, $context);

        return $this->updateDoc($docs);
    }

    public function updateDoc(array $docs): array
    {
        $docs['definitions']['PhoneNumber'] = [
            'type' => "string",
            "description" => "The phone number with country code",
            "example" => "+33654955566",
        ];

        return $docs;
    }


}
