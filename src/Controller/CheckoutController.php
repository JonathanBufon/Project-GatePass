<?php

namespace App\Controller;

use App\Entity\Pedido;
use App\Entity\Usuario;
use App\Form\PagamentoFormType;
use App\Service\PagamentoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/checkout')]
#[IsGranted('ROLE_USER')]
class CheckoutController extends AbstractController
{
    public function __construct(private readonly PagamentoService $pagamentoService)
    {
    }

    /**
     * Exibe a página de resumo (GET) e processa o pagamento (POST).
     * (Método existente)
     * @author Jonathan Bufon
     */
    #[Route('/{id}', name: 'app_checkout', methods: ['GET', 'POST'])]
    public function checkout(Request $request, Pedido $pedido): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // (Validações de segurança e status PENDENTE...)
        if ($pedido->getCliente() !== $usuario->getCliente() || $pedido->getStatus() !== 'PENDENTE') {
            // ... (Lógica de redirecionamento existente) ...
            $this->addFlash('error', 'Pedido não encontrado ou já finalizado.');
            return $this->redirectToRoute('app_home');
        }

        // (Lógica de formulário e POST...)
        $form = $this->createForm(PagamentoFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // ... (Lógica de chamada ao PagamentoService...) ...
            $sucesso = $this->pagamentoService->processarPagamento($pedido, $form->getData());

            if ($sucesso) {
                // Redireciona para a nova rota 'app_compra_confirmada'
                return $this->redirectToRoute('app_compra_confirmada', ['id' => $pedido->getId()]);
            }
            // ... (Tratamento de erro) ...
        }

        return $this->render('checkout/index.html.twig', [
            'pedido' => $pedido,
            'pagamentoForm' => $form->createView(),
        ]);
    }


    /**
     * Página de Confirmação de Compra (Template Jumbotron).
     * Esta rota é o destino após um pagamento APROVADO.
     *
     * @param Pedido $pedido O ParamConverter busca o Pedido pelo {id}
     */
    #[Route('/confirmacao/{id}', name: 'app_compra_confirmada', methods: ['GET'])]
    public function confirmacao(Pedido $pedido): Response
    {
        /** @var Usuario $usuario */
        $usuario = $this->getUser();

        // O usuário logado é o dono deste pedido?
        if ($pedido->getCliente() !== $usuario->getCliente()) {
            $this->addFlash('error', 'Acesso negado.');
            return $this->redirectToRoute('app_home');
        }

        // O pedido foi realmente APROVADO?
        // (Previne acesso direto à URL de um pedido PENDENTE ou RECUSADO)
        if ($pedido->getStatus() !== 'APROVADO') {
            $this->addFlash('warning', 'Este pedido ainda não foi aprovado.');
            // Se estiver pendente, manda de volta ao checkout
            if ($pedido->getStatus() === 'PENDENTE') {
                return $this->redirectToRoute('app_checkout', ['id' => $pedido->getId()]);
            }
            // Se falhou ou outro status, manda para home
            return $this->redirectToRoute('app_home');
        }

        return $this->render('checkout/confirmacao.html.twig', [
            'pedido' => $pedido
        ]);
    }
}