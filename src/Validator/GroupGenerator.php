<?php

namespace App\Validator;

use App\Entity\Entity;
use App\Entity\Thing;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;

/**
 * Class UserGroupGenerator
 * @package App\Validator
 */
final class GroupGenerator
{


    /**
     * @var Security
     */
    private $security;


    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * UserGroupGenerator constructor.
     * @param RequestStack $request
     * @param Security $security
     */
    public function __construct(RequestStack $request,Security $security)
    {
        $this->security = $security;
        $this->requestStack = $request;

    }

    /**
     * @param bool $normalization
     * @return string[]
     */
    public function generateGroups($normalization = false): array
    {

        $request = $this->requestStack->getCurrentRequest();

        $groups = $request->attributes->get("groups",[]);

        if(!$normalization) {
            $groups[] = "Default";
        }

        if(null === $request) {
            return $groups;
        }


        $uri = $request->getPathInfo();
        // Removing extension
        $ext = pathinfo($uri,PATHINFO_EXTENSION);
        $uri = str_replace(".$ext","",$uri);

        $route = $request->get('_route');

        $operation = strtolower($request->getMethod());

        $op_type = strpos($route,'collection') === false ? 'item' : 'collection';

        // Remove parameters at the end
        if($op_type === 'item') {
            $uri = preg_replace('#/[0-9a-zA-Z\-]+$#','',$uri);
        }

        $entity = basename($uri);
        $entity = pathinfo($entity,PATHINFO_FILENAME);

        $subresource = $request->get('_api_subresource_context',null);

        if($subresource) {

            $op_type = $subresource['collection'] ? 'collection' : 'item';

            if (isset($subresource['property'])) {
                $entity = Thing::decamelize($subresource['property']);
            }
        }

        // In post, and put, the return value is always an item
        if($operation != "get") {
            $op_type = 'item';
        }

        $type = $normalization ? "read" : "write";

        // entity
        $groups[] = $entity;
        $groups[] = "$entity:$operation";
        // item
        $groups[] = $op_type;
        // entity:item
        $groups[] = "$entity:$op_type";

        if($type) {
            // write
            $groups[] = $type;
            // entity:write
            $groups[] = "$entity:$type";
            // entity:item:read
            $groups[] = "$entity:$op_type:$type";
        }

        if($operation) {
            $groups[] = $operation;
        }

        return $groups;

    }




    public function prepareRequest()
    {

        $request = $this->requestStack->getCurrentRequest();

        if(!$request->get('_api_collection_operation_name')) {
            $request->attributes->set('_api_collection_operation_name',strtolower($request->getMethod()));
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
