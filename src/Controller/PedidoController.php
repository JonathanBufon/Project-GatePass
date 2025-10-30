<?php

namespace App\Controller;

use App\Entity\Lote;
use App\Service\PedidoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

/**
 * Controller "Magro" (Thin Controller) para o domínio de Pedidos.
 * @author Jonathan Bufon
 */
#[Route('/pedido')]
class PedidoController extends AbstractController
{
    public function __construct(private readonly PedidoService $pedidoService)
    {
    }

    /**
     * Rota que recebe o clique do botão "Comprar".
     *
     * @param Lote $lote O ParamConverter do Symfony busca o Lote pelo {id}
     */
    #[Route('/adicionar/{id}', name: 'app_pedido_adicionar', methods: ['GET'])]
    // Segurança explícita na rota (além do security.yaml)
    #[IsGranted('ROLE_USER')]
    public function adicionar(Lote $lote): Response
    {
        /** @var \App\Entity\Usuario $usuario */
        $usuario = $this->getUser(); // Pega o usuário logado

        try {
            $pedido = $this->pedidoService->adicionarLoteAoPedido($lote, $usuario);

            $this->addFlash('success', 'Ingresso adicionado ao seu pedido.');

            // Redireciona para o Checkout
            return $this->redirectToRoute('app_checkout', ['id' => $pedido->getId()]);

        } catch (\Exception $e) {
            // Trata erros de regra de negócio (ex: sem estoque, não é cliente)
            $this->addFlash('error', 'Erro ao adicionar ingresso: ' . $e->getMessage());

            return $this->redirectToRoute('app_evento_detalhe', ['id' => $lote->getEvento()->getId()]);
        }
    }
}