<?php

declare(strict_types=1);

namespace App\Serializer;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\Metadata\Resource\ResourceMetadataCollection;
use ApiPlatform\Serializer\SerializerContextBuilderInterface;
use Symfony\Component\HttpFoundation\Request;

use function Symfony\Component\String\u;

class SerialisationGroupGenerator implements SerializerContextBuilderInterface
{
    public function __construct(
        private readonly ResourceMetadataCollectionFactoryInterface $metadataFactory,
        private readonly SerializerContextBuilderInterface $decorated,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $groups = $this->buildGroupsFromRequest($request, $normalization);

        $context['groups'] = [...$context['groups'] ?? [], ...$groups];
        $context['enable_max_depth'] = true;
        $context['languages'] = $request->getLanguages();

        return $context;
    }

    private function buildGroupsFromRequest(Request $request, bool $normalization): array
    {
        $shortName = $this->extractShortName($request);

        if (!$shortName) {
            return [];
        }

        $operationType = $this->extractOperationType($request);
        $method = $this->extractMethod($request);
        $role = $this->getRole();
        $process = $normalization ? 'read' : 'write';

        return self::buildGroups(
            process: $process,
            shortName: $shortName,
            operationType: $operationType,
            method: $method,
            role: $role
        );
    }

    public static function buildGroups(
        string $process = 'unknown',
        string $shortName = 'unknown',
        string $operationType = 'unknown',
        string $method = 'unknown',
        string $role = 'unknown',
    ): array {
        $groups = [
            "$process", // read or write
            "$shortName", // user, product, etc.
            "$method", // get, post, etc.
            "$operationType", // collection, item, etc.
            "$role", // role_user, role_admin, etc.
            "$shortName:$process", // user:read, product:write, etc.
            "$shortName:$method", // user:get, product:post, etc.
            "$shortName:$operationType", // user:collection, product:item, etc.
            "$shortName:$operationType:$process", // user:collection, product:item, etc.
            "$role:$process", // user:read, admin:write, etc.
            "$role:$shortName", // role_user:read, role_admin:product, etc.
            "$role:$shortName:$process", // role_user:user:read, role_admin:product:write, etc.
            "$role:$shortName:$method", // role_user:user:get, role_admin:product:post, etc.
            "$role:$shortName:$operationType", // role_user:user:collection, role_admin:product:item, etc.
        ];

        return array_filter($groups, fn (string $group) => !str_contains($group, 'unknown'));
    }

    private function extractShortName(Request $request): ?string
    {
        $class = $request->attributes->get('_api_resource_class');

        if (!$class) {
            return null;
        }

        $metadata = $this->metadataFactory->create($class);

        return self::getShortName($metadata);
    }

    private function extractOperationType(Request $request): string
    {
        $route = $request->attributes->get('_route');

        $isCollection = preg_match('#collection$#', (string) $route);

        return $isCollection ? 'collection' : 'item';
    }

    private function extractMethod(Request $request): string
    {
        return strtolower($request->getMethod());
    }

    private function getRole(): string
    {
        return 'role_public';
    }

    public static function getShortName(ResourceMetadataCollection $resourceMetadata): string
    {
        $shortName = $resourceMetadata->getOperation()->getShortName();

        // https://symfony.com/doc/current/components/string.html#methods-to-change-case
        return u($shortName)->snake()->toString();
    }
}
