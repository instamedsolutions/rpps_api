<?php
namespace App\Serializer;
use ApiPlatform\Core\Serializer\SerializerContextBuilderInterface;
use App\Validator\GroupGenerator;
use App\Validator\UserGroupGenerator;
use Symfony\Component\HttpFoundation\Request;


/**
 *
 * This decorator is adding some serialization groups according to the role of the user
 *
 * Class AdminGroupsContextBuilder
 * @package App\Serializer
 */
final class GroupsContextBuilder implements SerializerContextBuilderInterface
{

    /**
     * @var SerializerContextBuilderInterface
     */
    private $decorated;


    /**
     * @var GroupGenerator
     */
    protected $groupGenerator;


    public function __construct(GroupGenerator $groupGenerator, SerializerContextBuilderInterface $decorated)
    {
        $this->decorated = $decorated;
        $this->groupGenerator = $groupGenerator;
    }

    /**
     * @param Request $request
     * @param bool $normalization
     * @param array|null $extractedAttributes
     * @return array
     */
    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {

        $context = $this->decorated->createFromRequest($request, $normalization, $extractedAttributes);

        $groups = $this->groupGenerator->generateGroups($normalization);

        $context['enable_max_depth'] = true;
        if(!isset($context['pagination_items_per_page'])) {
            $context['pagination_items_per_page'] = 15;
        }

        if(!isset($context['groups'])) {
            $context['groups'] = array();
        }

        $context['groups'] = array_merge($context['groups'],$groups);

        return $context;
    }

}
