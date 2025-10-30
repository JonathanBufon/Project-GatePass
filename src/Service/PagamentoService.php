<?php

namespace App\Service;

use App\Entity\Pedido;
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
        private readonly LoggerInterface $logger // Boa prática: logar transações
    ) {
    }

    /**
     * Processa o pagamento de um Pedido.
     *
     * @param array $dadosFormulario Dados do PagamentoFormType
     * @return bool True se o pagamento foi aprovado, False caso contrário.
     */
    public function processarPagamento(Pedido $pedido, array $dadosFormulario): bool
    {
        // 1. Regra de Negócio: Não reprocessar um pedido
        if ($pedido->getStatus() !== 'PENDENTE') {
            $this->logger->warning('Tentativa de reprocessar pedido já finalizado.', ['pedidoId' => $pedido->getId()]);
            return false;
        }

        //  SIMULAÇÃO DE GATEWAY
        // Aqui ocorreria a chamada real para um SDK (Stripe, PagSeguro)
        // ex: $gateway->charge($pedido->getValorTotal(), $dadosFormulario['ccNumber']);
        $this->logger->info('Iniciando processamento de pagamento...', [
            'pedidoId' => $pedido->getId(),
            'valor' => $pedido->getValorTotal(),
            'metodo' => $dadosFormulario['paymentMethod']
        ]);

        // Simples simulação: assume que o pagamento foi APROVADO
        $sucessoPagamento = true;
        // --- FIM DA SIMULAÇÃO ---

        if ($sucessoPagamento) {
            // 3. Regra de Negócio: Atualiza status (Camada 1 - Entidade)
            $pedido->setStatus('APROVADO');

            // Ativa os ingressos
            foreach ($pedido->getIngressos() as $ingresso) {
                if ($ingresso->getStatus() === 'RESERVADO') {
                    $ingresso->setStatus('DISPONIVEL'); // Pronto para uso (ex: QR Code)
                    $this->em->persist($ingresso);
                }
            }

            // (Aqui também dispararíamos um Evento/Email de Confirmação)

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