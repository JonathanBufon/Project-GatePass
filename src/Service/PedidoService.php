<?php

namespace App\Service;

use App\Entity\Cliente;
use App\Entity\Ingresso;
use App\Entity\Lote;
use App\Entity\Pedido;
use App\Entity\Usuario;
use App\Domain\Policy\EstoquePolicy;
use App\Domain\Policy\JanelaVendaPolicy;
use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Serviço de domínio responsável pelo ciclo de vida dos Pedidos.
 * Aplica regras de negócio para o carrinho (pendente) e finalização de compras.
 *
 * @author Jonathan Bufon
 */
class PedidoService
{
    private const STATUS_PENDENTE = 'PENDENTE';
    private const STATUS_PAGO = 'PAGO';
    private const STATUS_RESERVADO = 'RESERVADO';
    private const STATUS_CONFIRMADO = 'CONFIRMADO';

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly PedidoRepository $pedidoRepository,
        private readonly EstoquePolicy $estoquePolicy,
        private readonly JanelaVendaPolicy $janelaPolicy
    ) {
    }

    /**
     * Adiciona um ou mais ingressos (Lotes) ao pedido pendente do cliente.
     *
     * @throws AccessDeniedException Se o usuário não tiver cliente vinculado.
     * @throws \LogicException Se o lote não estiver disponível ou sem estoque.
     */
    public function adicionarLoteAoPedido(Lote $lote, Usuario $usuario, int $quantidade): Pedido
    {
        $cliente = $usuario->getCliente();
        if (!$cliente) {
            throw new AccessDeniedException('Ação permitida apenas para clientes.');
        }

        $pedido = $this->pedidoRepository->findPendentePorCliente($cliente) ?? $this->criarPedido($cliente);

        // A validação de estoque ocorrerá dentro deste método
        $this->adicionarIngressos($pedido, $lote, $quantidade);

        $pedido->recalcularTotal();

        $this->em->flush();

        return $pedido;
    }

    /**
     * Finaliza o pedido pendente, transformando-o em "PAGO".
     *
     * @throws \LogicException Se o pedido não estiver pendente ou o pagamento falhar.
     */
    public function finalizarPedido(Pedido $pedido, FormInterface $checkoutForm): Pedido
    {
        if ($pedido->getStatus() !== self::STATUS_PENDENTE) {
            throw new \LogicException('O pedido informado não está pendente.');
        }

        $dadosCheckout = $checkoutForm->getData();

        return $this->em->wrapInTransaction(function () use ($pedido, $dadosCheckout) {
            $this->processarPagamento($pedido, $dadosCheckout);
            $pedido->setStatus(self::STATUS_PAGO);
            $pedido->setDataPagamento(new \DateTime());

            foreach ($pedido->getIngressos() as $ingresso) {
                if ($ingresso->getStatus() === self::STATUS_RESERVADO) {
                    $ingresso->setStatus(self::STATUS_CONFIRMADO);
                }
            }

            return $pedido;
        });
    }

    /**
     * Retorna o pedido pendente (carrinho) do cliente atual.
     */
    public function getPedidoPendente(Usuario $usuario): ?Pedido
    {
        $cliente = $usuario->getCliente();
        return $cliente ? $this->pedidoRepository->findPendentePorCliente($cliente) : null;
    }

    /**
     * Retorna o histórico de pedidos do cliente autenticado.
     *
     * @return Pedido[]
     */
    public function getPedidosPorUsuario(Usuario $usuario): array
    {
        $cliente = $usuario->getCliente();
        if (!$cliente) {
            return [];
        }

        return $this->pedidoRepository->findBy(
            ['cliente' => $cliente],
            ['dataCriacao' => 'DESC']
        );
    }

    /**
     * Cria um novo pedido pendente para o cliente.
     */
    private function criarPedido(Cliente $cliente): Pedido
    {
        $pedido = new Pedido();
        $pedido->setCliente($cliente);
        $this->em->persist($pedido);

        return $pedido;
    }

    /**
     * Adiciona ingressos ao pedido, validando o estoque disponível.
     */
    private function adicionarIngressos(Pedido $pedido, Lote $lote, int $quantidade): void
    {
        if ($quantidade < 1) {
            throw new \LogicException('A quantidade deve ser pelo menos 1.');
        }

        // Políticas de domínio: janela de venda e estoque disponível
        if (!$this->janelaPolicy->dentroDaJanela($lote)) {
            throw new \LogicException('Lote fora da janela de venda.');
        }
        if (!$this->estoquePolicy->hasDisponibilidade($lote, $quantidade)) {
            $disp = $lote->getQuantidadeTotal() - $lote->getQuantidadeVendida();
            throw new \LogicException(sprintf('Estoque insuficiente para o lote. Solicitado: %d, Disponível: %d.', $quantidade, $disp));
        }

        for ($i = 0; $i < $quantidade; $i++) {
            $ingresso = (new Ingresso())
                ->setLote($lote)
                ->setValorPago($lote->getPreco())
                ->setStatus(self::STATUS_RESERVADO)
                ->setCodigoUnico($this->gerarCodigoIngresso());

            $pedido->addIngresso($ingresso);
            $this->em->persist($ingresso);
        }
    }

    /**
     * Mock de processamento de pagamento — simula sucesso.
     *
     * @throws \LogicException Se o pagamento falhar.
     */
    private function processarPagamento(Pedido $pedido, mixed $dadosCheckout): void
    {
        $pagamentoEfetuado = true; // Simulação de gateway externo.

        if (!$pagamentoEfetuado) {
            throw new \LogicException('Falha ao processar o pagamento.');
        }
    }

    /**
     * Gera um código único de ingresso.
     */
    private function gerarCodigoIngresso(): string
    {
        return uniqid('ING-') . bin2hex(random_bytes(5));
    }
}