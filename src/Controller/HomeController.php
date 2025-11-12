<?php

namespace App\Controller;

use App\Service\EventoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    public function __construct(private readonly EventoService $eventoService)
    {
    }

    #[Route('/', name: 'app_home', methods: ['GET'])]
    public function index(): Response
    {
        $banners = $this->eventoService->getBannersHome(3);
        $eventosDestaque = $this->eventoService->getEventosEmDestaque(4);

        return $this->render('home/index.html.twig', [
            'banners' => $banners,
            'eventosDestaque' => $eventosDestaque,
        ]);
    }
}
