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
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller "Magro" (Thin Controller) para o domínio de Pedidos.
 * Orquestra o fluxo de "Carrinho Persistente" (Pedido PENDENTE) e Checkout.
 * @author Jonathan Bufon
 */
#[Route('/pedido')]
// Segurança: Exige que o usuário esteja logado para todas as rotas deste controller.
#[IsGranted('ROLE_USER')]
class PedidoController extends AbstractController
{
    public function __construct(private readonly PedidoService $pedidoService)
    {
    }

    /**
     * Rota de Ação (POST) que recebe o clique do botão "Comprar".
     *
     * @param Lote $lote O ParamConverter do Symfony busca o Lote pelo {id}
     */
    #[Route('/adicionar/{id}', name: 'app_pedido_adicionar', methods: ['POST'])] // 1. Corrigido para POST
    public function adicionar(Request $request, Lote $lote): Response // 2. Adicionado Request
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // 3. Obter dados do formulário (Template Product)
        $quantidade = (int) $request->request->get('quantidade', 1);
        $token = $request->request->get('_token');
        $eventoId = $request->request->get('evento_id'); // Para redirect em caso de erro

        // 4. Segurança (CSRF): Valida o token
        $csrfTokenId = 'add-lote-' . $lote->getId();
        if (!$this->isCsrfTokenValid($csrfTokenId, $token)) {
            $this->addFlash('error', 'Requisição de segurança inválida.');
            return $this->redirectToRoute('app_evento_detalhe', ['id' => $eventoId]);
        }

        try {
            // 5. Passa a quantidade para o serviço
            $pedido = $this->pedidoService->adicionarLoteAoPedido($lote, $usuario, $quantidade);

            $this->addFlash('success', 'Ingresso(s) adicionado(s) ao seu pedido.');

            // 6. Redireciona para o Checkout (Nova Etapa 2.3)
            return $this->redirectToRoute('app_checkout_index');

        } catch (AccessDeniedException $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_evento_detalhe', ['id' => $lote->getEvento()->getId()]);
        } catch (\LogicException $e) {
            // Trata erros de regra de negócio (ex: sem estoque)
            $this->addFlash('error', 'Erro ao adicionar ingresso: ' . $e->getMessage());
            return $this->redirectToRoute('app_evento_detalhe', ['id' => $lote->getEvento()->getId()]);
        }
    }

    /**
     * Nova Etapa 2.3: Exibe a página de Checkout.
     * Mostra o Pedido PENDENTE (carrinho) e o formulário de dados.
     */
    #[Route('/checkout', name: 'app_checkout_index', methods: ['GET'])]
    public function checkout(): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $pedidoPendente = $this->pedidoService->getPedidoPendente($usuario);

        // Regra de Negócio: Não permitir checkout sem um "carrinho" (Pedido PENDENTE)
        if (!$pedidoPendente || $pedidoPendente->getIngressos()->isEmpty()) {
            $this->addFlash('warning', 'Seu carrinho está vazio.');
            return $this->redirectToRoute('app_home');
        }

        // DTO (Form) para dados do cliente
        $form = $this->createForm(CheckoutFormType::class);

        return $this->render('pedido/checkout.html.twig', [
            'pedido' => $pedidoPendente,
            'checkoutForm' => $form->createView()
        ]);
    }

    /**
     * Nova Etapa 2.4: Processa o POST do Checkout.
     * Finaliza o Pedido (converte 'PENDENTE' em 'PAGO').
     */
    #[Route('/checkout/submit', name: 'app_checkout_submit', methods: ['POST'])]
    public function submit(Request $request): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        $pedido = $this->pedidoService->getPedidoPendente($usuario);

        if (!$pedido) {
            // Se o usuário chegou aqui sem um pedido pendente (ex: limpou cache no meio do checkout)
            $this->addFlash('error', 'Seu carrinho expirou ou está vazio.');
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(CheckoutFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Delega a lógica transacional ao Serviço
                $pedidoFinalizado = $this->pedidoService->finalizarPedido($pedido, $form);

                $this->addFlash('success', 'Compra finalizada com sucesso!');

                // Redireciona para a Confirmação (Nova Etapa 2.5)
                return $this->redirectToRoute('app_pedido_confirmacao', ['id' => $pedidoFinalizado->getId()]);

            } catch (\LogicException $e) {
                // Erro (ex: pagamento mock falhou, pedido já pago)
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('app_checkout_index');
            }
        }

        // Se o formulário for inválido, renderiza o checkout novamente
        return $this->render('pedido/checkout.html.twig', [
            'pedido' => $pedido,
            'checkoutForm' => $form->createView()
        ]);
    }

    /**
     * Nova Etapa 2.5: Página de Confirmação (Jumbotron).
     * Refatora o 'index.html.twig' que você forneceu.
     */
    #[Route('/confirmado/{id}', name: 'app_pedido_confirmacao', methods: ['GET'])]
    public function confirmacao(Pedido $pedido): Response
    {
        // Segurança: Garante que o usuário logado só pode ver seus próprios pedidos.
        /** @var Usuario $usuario */
        $usuario = $this->getUser();
        if (!$usuario->getCliente() || $pedido->getCliente() !== $usuario->getCliente()) {
            throw new AccessDeniedHttpException('Você não tem permissão para visualizar este pedido.');
        }

        // Regra: Só deve ver a confirmação de pedidos PAGOS
        if ($pedido->getStatus() !== 'PAGO') {
            $this->addFlash('warning', 'Este pedido ainda não foi finalizado.');
            return $this->redirectToRoute('app_home'); // (Ou uma futura 'meus_pedidos')
        }

        // Renderiza o template que você forneceu (que será refatorado na Etapa 3.4)
        return $this->render('pedido/index.html.twig', [
            'pedido' => $pedido
        ]);
    }
}