<?php

namespace App\Repository;

use App\Entity\Evento;
use App\Entity\Vendedor; // Importar
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class EventoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Evento::class);
    }

    /**
     * Busca todos os eventos que pertencem
     * a um Vendedor específico.
     * @author Jonathan Bufon
     */
    public function findByVendedor(Vendedor $vendedor, array $orderBy = []): array
    {
        return $this->findBy(
            ['vendedor' => $vendedor],
            $orderBy
        );
    }
}