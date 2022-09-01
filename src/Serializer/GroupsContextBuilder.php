<?php

namespace App\Serializer;

use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Validator\GroupGenerator;
use Symfony\Component\HttpFoundation\Request;



final class GroupsContextBuilder implements SerializerContextBuilderInterface
{

    public function __construct(
        protected GroupGenerator $groupGenerator,
        private readonly SerializerContextBuilderInterface $decorated
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $groups = $this->groupGenerator->generateGroups($normalization);

        $context['enable_max_depth'] = true;
        if (!isset($context['pagination_items_per_page'])) {
            $context['pagination_items_per_page'] = 15;
        }

        if (!isset($context['groups'])) {
            $context['groups'] = [];
        }

        $context['groups'] = array_merge($context['groups'], $groups);

        return $context;
    }

}
