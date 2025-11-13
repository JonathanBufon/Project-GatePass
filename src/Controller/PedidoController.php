<?php

namespace App\Controller;

use App\Entity\Lote;
use App\Entity\Pedido;
use App\Entity\Usuario;
use App\Form\CheckoutFormType;
use App\Service\PedidoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller "magro" responsável por orquestrar o fluxo de pedidos (carrinho e checkout).
 *
 * @author Jonathan Bufon
 */
#[Route('/pedido')]
#[IsGranted('ROLE_USER')]
class PedidoController extends AbstractController
{
    public function __construct(
        private readonly PedidoService $pedidoService
    ) {
    }

    /**
     * Adiciona um lote ao pedido atual (carrinho persistente).
     */
    #[Route('/adicionar/{id}', name: 'app_pedido_adicionar', methods: ['POST'])]
    public function adicionar(Request $request, Lote $lote): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $quantidade = (int) $request->request->get('quantidade', 1);
        $token = $request->request->get('_token');
        $eventoId = $request->request->get('evento_id');

        $csrfTokenId = 'add-lote-' . $lote->getId();

        if (!$this->isCsrfTokenValid($csrfTokenId, $token)) {
            $this->addFlash('error', 'Requisição de segurança inválida.');
            return $this->redirectToRoute('app_evento_detalhe', ['id' => $eventoId]);
        }

        try {
            $this->pedidoService->adicionarLoteAoPedido($lote, $usuario, $quantidade);
            $this->addFlash('success', 'Ingresso(s) adicionado(s) ao seu pedido.');

            return $this->redirectToRoute('app_checkout_index');

        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
        } catch (\LogicException $e) {
            $this->addFlash('error', 'Erro ao adicionar ingresso: ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_evento_detalhe', [
            'id' => $lote->getEvento()->getId()
        ]);
    }

    /**
     * Exibe a página de checkout com o pedido pendente e formulário de dados.
     */
    #[Route('/checkout', name: 'app_checkout_index', methods: ['GET'])]
    public function checkout(): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $pedidoPendente = $this->pedidoService->getPedidoPendente($usuario);

        if (!$pedidoPendente || $pedidoPendente->getIngressos()->isEmpty()) {
            $this->addFlash('warning', 'Seu carrinho está vazio.');
            return $this->redirectToRoute('app_home');
        }

        if (method_exists($pedidoPendente, 'isExpirado') && $pedidoPendente->isExpirado()) {
            $this->addFlash('warning', 'Sua reserva expirou. Os ingressos foram liberados. Selecione novamente os ingressos.');
            return $this->redirectToRoute('app_evento_index');
        }

        $form = $this->createForm(CheckoutFormType::class);

        return $this->render('pedido/checkout.html.twig', [
            'pedido' => $pedidoPendente,
            'checkoutForm' => $form->createView(),
        ]);
    }

    /**
     * Processa o envio do formulário de checkout, finalizando o pedido.
     */
    #[Route('/checkout/submit', name: 'app_checkout_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        $pedido = $this->pedidoService->getPedidoPendente($usuario);

        if (!$pedido) {
            $this->addFlash('error', 'Seu carrinho expirou ou está vazio.');
            return $this->redirectToRoute('app_home');
        }

        if (method_exists($pedido, 'isExpirado') && $pedido->isExpirado()) {
            $this->addFlash('warning', 'Não foi possível finalizar: a reserva do seu pedido expirou. Selecione novamente os ingressos.');
            return $this->redirectToRoute('app_evento_index');
        }

        $form = $this->createForm(CheckoutFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $pedidoFinalizado = $this->pedidoService->finalizarPedido($pedido, $form);
                $this->addFlash('success', 'Compra finalizada com sucesso!');

                return $this->redirectToRoute('app_pedido_detalhe', [
                    'id' => $pedidoFinalizado->getId(),
                ]);
            } catch (\LogicException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_checkout_index');
            }
        }

        return $this->render('pedido/checkout.html.twig', [
            'pedido' => $pedido,
            'checkoutForm' => $form->createView(),
        ]);
    }

    /**
     * Exibe os detalhes de um pedido finalizado (somente do usuário autenticado).
     */
    #[Route('/detalhe/{id}', name: 'app_pedido_detalhe', methods: ['GET'])]
    public function detalhe(Pedido $pedido): Response
    {
        $this->denyAccessUnlessGranted('PEDIDO_VIEW', $pedido);

        return $this->render('pedido/detalhe.html.twig', [
            'pedido' => $pedido,
        ]);
    }

    /**
     * TAREFA 3: Exibe a lista de "Meus Pedidos" do usuário autenticado.
     */
    #[Route('/meus-pedidos', name: 'app_cliente_pedidos_index', methods: ['GET'])]
    public function meusPedidos(): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // Delega ao Serviço (que já contém a lógica de busca e ordenação)
        $pedidos = $this->pedidoService->getPedidosPorUsuario($usuario);

        // Renderiza a nova View (Tarefa 4)
        return $this->render('pedido/meus_pedidos.html.twig', [
            'pedidos' => $pedidos,
        ]);
    }
}