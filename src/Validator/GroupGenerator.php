<?php

namespace App\Validator;

use App\Entity\Entity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

use function Symfony\Component\String\u;

final class GroupGenerator
{
    public function __construct(protected RequestStack $requestStack)
    {
    }

    /**
     * @return string[]
     */
    public function generateGroups(bool $normalization = false): array
    {
        $request = $this->requestStack->getCurrentRequest();

        $groups = $request->attributes->get('groups', []);

        if (!$normalization) {
            $groups[] = 'Default';
        }

        if (!$request instanceof Request) {
            return $groups;
        }

        $uri = $request->getPathInfo();
        // Removing extension
        $ext = pathinfo($uri, PATHINFO_EXTENSION);
        $uri = str_replace(".$ext", '', $uri);

        $route = $request->get('_route');

        $operation = strtolower($request->getMethod());

        $op_type = str_contains((string) $route, 'collection') ? 'collection' : 'item';

        // Remove parameters at the end
        if ('item' === $op_type) {
            $uri = preg_replace('#/[0-9a-zA-Z\-]+$#', '', $uri);
        }

        $entity = basename($uri);
        $entity = pathinfo($entity, PATHINFO_FILENAME);

        $subresource = $request->get('_api_subresource_context', null);

        if ($subresource) {
            $op_type = $subresource['collection'] ? 'collection' : 'item';

            if (isset($subresource['property'])) {
                $entity = u($subresource['property'])->snake()->toString();
            }
        }

        // In post, and put, the return value is always an item
        if ('get' != $operation) {
            $op_type = 'item';
        }

        $type = $normalization ? 'read' : 'write';

        // entity
        $groups[] = $entity;
        $groups[] = "$entity:$operation";
        // item
        $groups[] = $op_type;
        // entity:item
        $groups[] = "$entity:$op_type";

        // write
        $groups[] = $type;
        // entity:write
        $groups[] = "$entity:$type";
        // entity:item:read
        $groups[] = "$entity:$op_type:$type";

        if ($operation) {
            $groups[] = $operation;
        }

        return $groups;
    }

    public function prepareRequest(): void
    {
        $request = $this->requestStack->getCurrentRequest();

        if (!$request->get('_api_collection_operation_name')) {
            $request->attributes->set('_api_collection_operation_name', strtolower($request->getMethod()));
        }
    }

    /**
     * @return array|string[]
     */
    public function __invoke(): array
    {
        return $this->generateGroups();
    }
}
