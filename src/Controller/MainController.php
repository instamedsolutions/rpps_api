<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MainController extends AbstractController
{
    #[Route(path: '/', name: 'main')]
    #[Route(path: '/index.html', name: 'index')]
    public function index(): Response
    {
        return $this->redirectToRoute('api_entrypoint');
    }
}
