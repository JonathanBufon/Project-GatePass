<?php

namespace App\Service;

use App\Entity\Ingresso;
use App\Entity\Lote;
use App\Entity\Pedido;
use App\Entity\Usuario;
use App\Form\CheckoutFormType;
use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Camada de Serviço (Lógica de Negócio) para o domínio de Vendas.
 * Responsável por orquestrar a criação e modificação de Pedidos.
 * @author Jonathan Bufon
 */
class PedidoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PedidoRepository $pedidoRepository
    ) {
    }

    /**
     * Orquestra a adição de Lotes (produtos) a um Pedido (carrinho).
     *
     * @param int $quantidade O número de ingressos a criar.
     * @throws AccessDeniedException Se o Usuário não for um Cliente.
     * @throws \LogicException Regras de negócio (ex: estoque esgotado).
     */
    public function adicionarLoteAoPedido(Lote $lote, Usuario $usuario, int $quantidade): Pedido
    {
        $cliente = $usuario->getCliente();
        if (!$cliente) {
            throw new AccessDeniedException('Ação permitida apenas para clientes.');
        }

        // Regra de Negócio: (Futura) Verificar se o Lote tem estoque
        // $ingressosVendidos = $lote->getIngressos()->count();
        // if (($ingressosVendidos + $quantidade) > $lote->getQuantidadeTotal()) {
        //     throw new \LogicException('Estoque esgotado para a quantidade solicitada.');
        // }

        $pedido = $this->pedidoRepository->findPendentePorCliente($cliente);

        if (!$pedido) {
            $pedido = new Pedido();
            $pedido->setCliente($cliente);
            // O status 'PENDENTE' é definido no construtor do Pedido (presumindo)
            $this->em->persist($pedido);
        }

        // TAREFA 1.1: Criar múltiplos ingressos
        for ($i = 0; $i < $quantidade; $i++) {
            $ingresso = new Ingresso();
            $ingresso->setLote($lote);
            $ingresso->setValorPago($lote->getPreco()); // Preço no momento da compra
            $ingresso->setStatus('RESERVADO'); // Status até o pagamento
            $ingresso->setCodigoUnico(uniqid('ING-') . bin2hex(random_bytes(5)));

            $pedido->addIngresso($ingresso);
            // (Se não houver cascade: persist, persistimos manualmente)
            $this->em->persist($ingresso);
        }

        $pedido->recalcularTotal();

        $this->em->flush();

        return $pedido;
    }

    /**
     * TAREFA 1.3: Converte o Pedido 'PENDENTE' (Carrinho) em 'PAGO' (Venda).
     * Executa a lógica de negócio transacional.
     *
     * @param Pedido $pedido O pedido pendente (carrinho) a ser finalizado.
     * @param FormInterface $checkoutForm O DTO com os dados do cliente e pagamento.
     * @throws \LogicException Se o pedido não puder ser finalizado.
     */
    public function finalizarPedido(Pedido $pedido, FormInterface $checkoutForm): Pedido
    {
        if ($pedido->getStatus() !== 'PENDENTE') {
            throw new \LogicException('Este pedido não está pendente e não pode ser finalizado.');
        }

        $dadosCheckout = $checkoutForm->getData();

        // Esta função garante que todas as operações de BD (flush)
        // só sejam comitadas se a função inteira rodar sem exceções.
        return $this->em->wrapInTransaction(function () use ($pedido, $dadosCheckout) {

            // 1. (Mock) Simula o processamento do pagamento
            // $gatewayPagamento = new PagamentoGateway();
            // $sucesso = $gatewayPagamento->processar($pedido->getTotal(), $dadosCheckout['formaPagamento'], ...);
            $sucessoPagamento = true; // Mock

            if (!$sucessoPagamento) {
                throw new \LogicException('O pagamento falhou.');
            }

            // 2. Atualiza o Pedido
            $pedido->setStatus('PAGO');
            $pedido->setDataPagamento(new \DateTime());
            // (Opcional) Salva os dados do comprador (Nome/CPF) no Pedido
            // $pedido->setNomeComprador($dadosCheckout['nomeCompleto']);

            // 3. Atualiza os Ingressos (de 'RESERVADO' para 'CONFIRMADO')
            foreach ($pedido->getIngressos() as $ingresso) {
                if ($ingresso->getStatus() === 'RESERVADO') {
                    $ingresso->setStatus('CONFIRMADO');
                }
            }

            // 4. Flush transacional
            // $this->em->flush(); // O wrapInTransaction cuida do flush

            return $pedido;
        });
    }

    /**
     * Busca o pedido pendente (carrinho) do cliente.
     */
    public function getPedidoPendente(Usuario $usuario): ?Pedido
    {
        if (!$usuario->getCliente()) {
            return null;
        }
        return $this->pedidoRepository->findPendentePorCliente($usuario->getCliente());
    }
}