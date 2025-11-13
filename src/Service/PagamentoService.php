<?php

namespace App\Service;

use App\Entity\Pedido;
use App\Dto\CheckoutDto;
use App\Message\PedidoPagoMessage;
use App\Port\Payment\PaymentGatewayInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Camada de Serviço (Lógica de Negócio) para Pagamentos.
 * SRP: Encapsula a lógica de interação com gateways de pagamento.
 * @author Joanthan Bufon
 */
class PagamentoService
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
        private readonly PaymentGatewayInterface $gateway,
        private readonly \Symfony\Component\Messenger\MessageBusInterface $bus
    ) {
    }

    /**
     * Processa o pagamento de um Pedido.
     *
     * @param array $dadosFormulario Dados do PagamentoFormType
     * @return bool True se o pagamento foi aprovado, False caso contrário.
     */
    public function processarPagamento(Pedido $pedido, array|CheckoutDto $dadosFormulario): bool
    {
        // 1. Regra de Negócio: Não reprocessar um pedido
        if ($pedido->getStatus() !== 'PENDENTE') {
            $this->logger->warning('Tentativa de reprocessar pedido já finalizado.', ['pedidoId' => $pedido->getId()]);
            return false;
        }

        $this->logger->info('Iniciando processamento de pagamento...', [
            'pedidoId' => $pedido->getId(),
            'valor' => $pedido->getValorTotal(),
            'metodo' => is_array($dadosFormulario) ? ($dadosFormulario['formaPagamento'] ?? 'n/a') : ($dadosFormulario->formaPagamento ?? 'n/a')
        ]);
        $dto = is_array($dadosFormulario) ? (function(array $a){ $d=new CheckoutDto(); $d->nomeCompleto=$a['nomeCompleto']??''; $d->email=$a['email']??''; $d->cpf=$a['cpf']??''; $d->formaPagamento=$a['formaPagamento']??''; return $d;})($dadosFormulario) : $dadosFormulario;
        $sucessoPagamento = $this->gateway->charge($pedido, $dto);

        if ($sucessoPagamento) {
            // 3. Regra de Negócio: Atualiza status (Camada 1 - Entidade)
            $pedido->setStatus('PAGO');

            // Dispara mensagem assíncrona para geração/entrega de ingressos
            $this->bus->dispatch(new PedidoPagoMessage($pedido->getId()));

        } else {
            // 4. Pagamento falhou
            $pedido->setStatus('RECUSADO');
        }

        // 5. Persistência
        $this->em->persist($pedido);
        $this->em->flush();

        $this->logger->info('Processamento finalizado.', ['pedidoId' => $pedido->getId(), 'status' => $pedido->getStatus()]);

        return $sucessoPagamento;
    }
}