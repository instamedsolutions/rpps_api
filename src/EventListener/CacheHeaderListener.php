<?php

namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\EventListener\AbstractSessionListener;

class CacheHeaderListener
{
    public function onKernelResponse(ResponseEvent $event): void
    {
        // Get the response object
        $response = $event->getResponse();

        // Add the custom header
        $response->headers->set(AbstractSessionListener::NO_AUTO_CACHE_CONTROL_HEADER, 'true');
    }
}
