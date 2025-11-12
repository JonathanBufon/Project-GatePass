<?php

namespace App\Port\Email;

use App\Entity\Pedido;

interface TransactionalEmailInterface
{
    public function sendPurchaseConfirmation(Pedido $pedido): void;
}
