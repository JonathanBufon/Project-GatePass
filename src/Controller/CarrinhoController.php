<?php

namespace App\Controller;

use App\Form\CheckoutFormType;
use App\Service\CarrinhoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Controller "Magro" (Thin Controller) para o fluxo de Carrinho e Checkout.
 * SRP: Orquestra o CarrinhoService (Sessão) e a exibição da página de checkout.
 */
class CarrinhoController extends AbstractController
{
    /**
     * Exibe a página de Checkout (Template Checkout).
     *
     * Busca os itens do CarrinhoService (Sessão), calcula o total
     * e renderiza o formulário de checkout (DTO).
     */
    #[Route('/checkout', name: 'app_checkout_index', methods: ['GET'])]
    public function index(CarrinhoService $carrinhoService): Response
    {
        $carrinhoDetalhado = $carrinhoService->getItensDetalhado();

        // Regra de Negócio: Não permitir checkout com carrinho vazio
        if (empty($carrinhoDetalhado['itens'])) {
            $this->addFlash('warning', 'Seu carrinho está vazio.');
            return $this->redirectToRoute('app_home');
        }

        // Este DTO coletará os dados do cliente (nome, cpf, pagamento)
        $form = $this->createForm(CheckoutFormType::class);

        return $this->render('pedido/checkout.html.twig', [
            'itens' => $carrinhoDetalhado['itens'],
            'total' => $carrinhoDetalhado['total'],
            'checkoutForm' => $form->createView()
        ]);
    }

    /**
     * Adiciona um Lote ao carrinho.
     * Rota de Ação (POST) protegida por CSRF.
     */
    #[Route('/carrinho/add/{id}', name: 'app_carrinho_add', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function add(Request $request, int $id, CarrinhoService $carrinhoService): Response
    {
        $quantidade = (int) $request->request->get('quantidade', 1);
        $token = $request->request->get('_token');

        // Segurança (CSRF): Valida o token
        $csrfTokenId = 'add-lote-' . $id;
        if (!$this->isCsrfTokenValid($csrfTokenId, $token)) {
            $this->addFlash('error', 'Requisição de segurança inválida.');
            return $this->redirectToRoute('app_home');
        }

        try {
            $carrinhoService->add($id, $quantidade);
            $this->addFlash('success', 'Item adicionado ao carrinho.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erro ao adicionar item: ' . $e->getMessage());

            // Tenta redirecionar de volta ao evento, se a info foi passada
            $eventoId = $request->request->get('evento_id');
            if ($eventoId) {
                return $this->redirectToRoute('app_evento_detalhe', ['id' => $eventoId]);
            }
            return $this->redirectToRoute('app_home');
        }

        return $this->redirectToRoute('app_checkout_index');
    }

    // O método submit() (Etapa 4, Tarefa 2) será adicionado aqui posteriormente.
}