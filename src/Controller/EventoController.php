<?php

namespace App\Controller;

use App\Service\EventoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller "Magro" (Thin Controller) para o domínio de Eventos.
 * Respeita o SRP: Apenas orquestra, delega a lógica ao EventoService.
 * @author Jonathan Bufon
 */
class EventoController extends AbstractController
{
    public function __construct(private readonly EventoService $eventoService)
    {
    }

    /**
     * Define a rota principal (Homepage) do GatePass.
     * Esta rota exibe a "Listagem de Eventos" (template Album).
     */
    #[Route('/eventos', name: 'app_evento_index', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $filtros = [
            'q' => trim((string) $request->query->get('q', '')),
            'local' => trim((string) $request->query->get('local', '')),
            'dataInicio' => $request->query->get('dataInicio'),
            'dataFim' => $request->query->get('dataFim'),
        ];

        $eventos = $this->eventoService->searchEventosPublicados($filtros);

        $response = $this->render('evento/index.html.twig', [
            'eventos' => $eventos,
            'filtros' => $filtros,
        ]);
        $response->setPublic();
        $response->setMaxAge(120);
        return $response;
    }

    /**
     * NOVO MÉTODO: Página de Detalhe do Evento (Template Product).
     *
     * Utiliza o {id} da URL para buscar o evento.
     */

    #[Route('/evento/{id}', name: 'app_evento_detalhe', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function detalhe(int $id): Response
    {
        $evento = $this->eventoService->findEventoPublicado($id);
        // Se o serviço retornar null (não achou ou não está 'PUBLICADO'),
        // lançamos uma exceção 404 Not Found.
        if (!$evento) {
            throw $this->createNotFoundException('O evento solicitado não foi encontrado ou não está disponível.');
        }
        $response = $this->render('evento/detalhe.html.twig', [
            'evento' => $evento,
        ]);
        $response->setPublic();
        $response->setMaxAge(120);
        return $response;
    }

}
