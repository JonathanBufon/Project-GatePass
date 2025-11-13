<?php

namespace App\Controller\Vendedor;

use App\Entity\Usuario;
use App\Service\EventoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Define o módulo de Vendedor, protegido por ROLE_VENDEDOR
 * (definido no security.yaml).
 * @author Jonathan Bufon
 */
#[Route('/vendedor')]
#[IsGranted('ROLE_VENDEDOR')] // Segurança explícita no Controller
class DashboardController extends AbstractController
{
    public function __construct(private readonly EventoService $eventoService)
    {
    }

    /**
     * Rota principal (Homepage) do Vendedor.
     * Template "Dashboard"
     */
    #[Route('/dashboard', name: 'app_vendedor_dashboard', methods: ['GET'])]
    public function index(): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $vendedor = $usuario->getVendedor();
        if (!$vendedor) {
            $this->addFlash('error', 'Perfil de vendedor não encontrado.');
            return $this->redirectToRoute('app_home');
        }

        $eventos = $this->eventoService->getEventosPorVendedor($vendedor);

        $totalEventos = count($eventos);
        $publicados = 0; $rascunhos = 0; $ingressosAtivos = 0; $totalLotes = 0;
        foreach ($eventos as $ev) {
            if ($ev->getStatus() && $ev->getStatus()->name === 'PUBLICADO') { $publicados++; }
            if ($ev->getStatus() && $ev->getStatus()->name === 'RASCUNHO') { $rascunhos++; }
            $totalLotes += $ev->getLotes()->count();
            foreach ($ev->getLotes() as $l) { if (method_exists($l, 'getQuantidadeVendida')) { $ingressosAtivos += $l->getQuantidadeVendida(); } }
        }

        return $this->render('vendedor/dashboard/index.html.twig', [
            'eventos' => $eventos,
            'vendedor' => $vendedor,
            'stats' => [
                'totalEventos' => $totalEventos,
                'publicados' => $publicados,
                'rascunhos' => $rascunhos,
                'totalLotes' => $totalLotes,
                'ingressosAtivos' => $ingressosAtivos,
            ],
        ]);
    }
}