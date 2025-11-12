<?php

namespace App\Adapter\Payment;

use App\Dto\CheckoutDto;
use App\Entity\Pedido;
use App\Port\Payment\PaymentGatewayInterface;
use Psr\Log\LoggerInterface;

class SandboxPaymentGateway implements PaymentGatewayInterface
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function charge(Pedido $pedido, CheckoutDto $checkout): bool
    {
        $this->logger->info('Sandbox charge called', [
            'pedido' => $pedido->getId(),
            'valor' => $pedido->getValorTotal(),
            'metodo' => $checkout->formaPagamento ?? 'n/a'
        ]);
        return true;
    }
}
