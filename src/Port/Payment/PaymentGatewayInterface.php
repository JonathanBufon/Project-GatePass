<?php

namespace App\Port\Payment;

use App\Entity\Pedido;
use App\Dto\CheckoutDto;

interface PaymentGatewayInterface
{
    public function charge(Pedido $pedido, CheckoutDto $checkout): bool;
}
