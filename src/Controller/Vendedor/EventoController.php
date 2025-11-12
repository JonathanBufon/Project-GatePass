<?php

namespace App\Controller\Vendedor;

use App\Entity\Evento;
use App\Entity\Usuario;
use App\Form\EventoFormType;
use App\Service\EventoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller "Magro" (Thin Controller) para o Módulo do Vendedor.
 * Gerencia o CRUD de Eventos do Vendedor logado.
 */
#[Route('/vendedor/evento')]
#[IsGranted('ROLE_VENDEDOR')]
class EventoController extends AbstractController
{
    /**
     * Orquestra a criação de um novo evento (GET para formulário, POST para salvar).
     */
    #[Route('/novo', name: 'app_vendedor_evento_novo', methods: ['GET', 'POST'])]
    public function novo(Request $request, EventoService $eventoService): Response
    {
        $evento = new Evento();
        $form = $this->createForm(EventoFormType::class, $evento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            /** @var Usuario $usuario */
            $usuario = $this->getUser();
            $vendedor = $usuario->getVendedor();

            if (!$vendedor) {
                $this->addFlash('error', 'Usuário autenticado não está associado a um Vendedor.');
                return $this->redirectToRoute('app_vendedor_dashboard');
            }

            $eventoService->criarNovoEvento($evento, $vendedor);

            $this->addFlash('success', 'Evento "' . $evento->getNome() . '" foi criado como rascunho.');

            return $this->redirectToRoute('app_vendedor_dashboard');
        }

        return $this->render('vendedor/evento/novo.html.twig', [
            'eventoForm' => $form->createView(),
            'page_title' => 'Criar Novo Evento'
        ]);
    }

    /**
     * Orquestra a edição de um evento existente.
     */
    #[Route('/{id}/editar', name: 'app_vendedor_evento_editar', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editar(Request $request, Evento $evento, EventoService $eventoService): Response
    {
        // Regra de Negócio (Segurança): Garantir que o vendedor logado é o dono do evento.
        $this->denyAccessUnlessGranted('EVENTO_EDIT', $evento);

        $form = $this->createForm(EventoFormType::class, $evento);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $eventoService->atualizarEvento($evento);

            $this->addFlash('success', 'Evento "' . $evento->getNome() . '" foi atualizado.');

            return $this->redirectToRoute('app_vendedor_dashboard');
        }

        return $this->render('vendedor/evento/novo.html.twig', [
            'eventoForm' => $form->createView(),
            'page_title' => 'Editar Evento: ' . $evento->getNome()
        ]);
    }

    /**
     * TAREFA 2: Orquestra a ação de "Publicar" um evento.
     * Esta rota aceita apenas POST e é protegida por CSRF.
     */
    #[Route('/{id}/publicar', name: 'app_vendedor_evento_publicar', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function publicar(Request $request, Evento $evento, EventoService $eventoService): Response
    {
        // Regra de Negócio (Segurança): Garantir que o vendedor logado é o dono do evento.
        $this->denyAccessUnlessGranted('EVENTO_PUBLICAR', $evento);

        // Segurança (CSRF): Validar o token enviado pelo formulário no dashboard.
        $token = $request->request->get('_token');
        if (!$this->isCsrfTokenValid('publicar' . $evento->getId(), $token)) {
            $this->addFlash('error', 'Token de segurança inválido.');
            return $this->redirectToRoute('app_vendedor_dashboard');
        }

        try {
            // Delega a lógica de negócio (regras de transição de estado) para o Serviço
            $eventoService->publicarEvento($evento);
            $this->addFlash('success', 'Evento "' . $evento->getNome() . '" foi publicado com sucesso.');

        } catch (\LogicException $e) {
            // Captura falhas das regras de negócio (ex: sem lotes)
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('app_vendedor_dashboard');
    }
}