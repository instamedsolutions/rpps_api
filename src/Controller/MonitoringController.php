<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MonitoringController extends AbstractController
{
    #[Route(path: '/api/monitoring/health_check', name: 'api_monitoring_health_check')]
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
