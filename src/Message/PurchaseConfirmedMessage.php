<?php

namespace App\Message;

final class PurchaseConfirmedMessage
{
    public function __construct(public readonly int $pedidoId) {}
}
