<?php

namespace App\Domain\Policy;

use App\Entity\Lote;

class JanelaVendaPolicy
{
    public function dentroDaJanela(Lote $lote, \DateTimeInterface $quando = new \DateTime()): bool
    {
        $inicio = $lote->getDataInicioVendas();
        $fim = $lote->getDataFimVendas();
        if ($inicio && $quando < $inicio) { return false; }
        if ($fim && $quando > $fim) { return false; }
        return true;
    }
}
