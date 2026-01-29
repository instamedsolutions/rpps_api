<?php

/*
 * This file is part of the API Platform project.
 *
 * (c) Kévin Dunglas <dunglas@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace App\ApiPlatform\Routing;

use ApiPlatform\Api\IdentifiersExtractorInterface as LegacyIdentifiersExtractorInterface;
use ApiPlatform\Api\ResourceClassResolverInterface as LegacyResourceClassResolverInterface;
use ApiPlatform\Api\UriVariablesConverterInterface as LegacyUriVariablesConverterInterface;
use ApiPlatform\Metadata\IdentifiersExtractorInterface;
use ApiPlatform\Metadata\IriConverterInterface;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Operation\Factory\OperationMetadataFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\Factory\ResourceNameCollectionFactoryInterface;
use ApiPlatform\Metadata\ResourceClassResolverInterface;
use ApiPlatform\Metadata\UriVariablesConverterInterface;
use ApiPlatform\Metadata\UrlGeneratorInterface;
use ApiPlatform\Metadata\Util\ClassInfoTrait;
use ApiPlatform\Metadata\Util\ResourceClassInfoTrait;
use ApiPlatform\State\ProviderInterface;
use ApiPlatform\State\UriVariablesResolverTrait;
use App\Entity\Entity;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Contracts\Cache\CacheInterface;

/**
 * @author Antoine Bluchet <soyuka@gmail.com>
 */
class IriConverter implements IriConverterInterface
{
    use ClassInfoTrait;
    use ResourceClassInfoTrait;
    use UriVariablesResolverTrait;

    private $localOperationCache = [];
    private $localIdentifiersExtractorOperationCache = [];

    public function __construct(
        private readonly ResourceNameCollectionFactoryInterface $resourceNameCollectionFactory,
        private readonly ProviderInterface $provider,
        private readonly RouterInterface $router,
        private readonly IdentifiersExtractorInterface|LegacyIdentifiersExtractorInterface $identifiersExtractor,
        ResourceClassResolverInterface|LegacyResourceClassResolverInterface $resourceClassResolver,
        private readonly ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory,
        UriVariablesConverterInterface|LegacyUriVariablesConverterInterface|null $uriVariablesConverter = null,
        private readonly ?IriConverterInterface $decorated = null,
        private readonly ?OperationMetadataFactoryInterface $operationMetadataFactory = null,
        private readonly CacheInterface $cache,
    ) {
        $this->resourceClassResolver = $resourceClassResolver;
        $this->uriVariablesConverter = $uriVariablesConverter;
    }

    public function getResourceFromIri(string $iri, array $context = [], ?Operation $operation = null): object
    {
        if (preg_match('#[a-z]{3}_[0-9a-f]{16}#', $iri)) {
            return $this->getResourceFromCustomId($iri, $context, $operation);
        }

        return $this->decorated->getResourceFromIri($iri, $context, $operation);
    }

    public function getIriFromResource(object|string $resource, int $referenceType = UrlGeneratorInterface::ABS_PATH, ?Operation $operation = null, array $context = []): ?string
    {
        if ($resource instanceof Entity) {
            return $resource->getEntityId();
        }

        return $this->decorated->getIriFromResource($resource, $referenceType, $operation, $context);
    }

    public function getResourceFromCustomId($iri, array $context = [], ?Operation $operation = null): object
    {
        if (!preg_match('#[a-z]{3}_[0-9a-f]{16}#', $iri)) {
            return $this->decorated->getResourceFromCustomId($iri, $context, $operation);
        }

        $prefix = explode('_', $iri);

        $className = $this->getClassNameFromPrefix($prefix[0]);

        if ($className) {
            // Transform back to uuid
            $iri = explode('_', $iri)[1];
            // Add the - to match the uuid format
            $iri = substr($iri, 0, 8) . '-' . substr($iri, 8, 4) . '-' . substr($iri, 12, 4) . '-' . substr(
                $iri,
                16,
                4
            ) . '-' . substr($iri, 20, 12);

            return $this->provider->provide($operation, [
                'id' => $iri,
            ], $context);
        }

        return $this->decorated->getResourceFromIri($iri, $context, $operation);
    }
}
