<?php

namespace App\Message;

class PedidoPagoMessage
{
    public function __construct(private readonly int $pedidoId)
    {
    }

    public function getPedidoId(): int
    {
        return $this->pedidoId;
    }
}
