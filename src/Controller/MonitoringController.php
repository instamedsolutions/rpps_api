<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonitoringController extends AbstractController
{
    /**
     * @Route("/api/monitoring/health_check", name="api_monitoring_health_check")
     *
     * @return Response
     */
    public function healthCheckAction(): Response
    {
        return $this->json([
            'healthy' => true,
            'services' => [
                'api' => 'running',
                'application' => 'running'
            ]
        ]);
    }
}
