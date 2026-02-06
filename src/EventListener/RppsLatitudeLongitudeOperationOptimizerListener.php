<?php

declare(strict_types=1);

namespace App\EventListener;

use ApiPlatform\Metadata\Resource\Factory\ResourceMetadataCollectionFactoryInterface;
use ApiPlatform\State\Util\OperationRequestInitiatorTrait;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * This is used for the search with latitude & longitude, to remove the calcul of the number of total items in the query
 * It reduces the load time from 8s to 320ms.
 */
final class RppsLatitudeLongitudeOperationOptimizerListener
{
    use OperationRequestInitiatorTrait;

    public function __construct(
        ?ResourceMetadataCollectionFactoryInterface $resourceMetadataCollectionFactory = null,
    ) {
        $this->resourceMetadataCollectionFactory = $resourceMetadataCollectionFactory;
    }

    /**
     * Calls the data provider and sets the data attribute.
     *
     * @throws NotFoundHttpException
     */
    public function onKernelRequest(RequestEvent $event): void
    {
        $request = $event->getRequest();

        if ('_api_/rpps{._format}_get_collection' != $request->attributes->get('_route')) {
            return;
        }

        if (!$request->query->get('latitude') || !$request->query->get('longitude')) {
            return;
        }

        $operation = $this->initializeOperation($request);

        $operation = $operation->withPaginationPartial(true);
        $operation = $operation->withPaginationClientPartial(true);
        $operation = $operation->withPaginationClientItemsPerPage(false);
        $operation = $operation->withPaginationClientEnabled(false);

        $request->attributes->set('_api_operation', $operation);
    }
}
