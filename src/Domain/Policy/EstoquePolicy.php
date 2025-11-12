<?php

namespace App\Domain\Policy;

use App\Entity\Lote;

class EstoquePolicy
{
    public function hasDisponibilidade(Lote $lote, int $quantidade): bool
    {
        $disponivel = $lote->getQuantidadeTotal() - $lote->getQuantidadeVendida();
        return $quantidade > 0 && $quantidade <= $disponivel;
    }
}
