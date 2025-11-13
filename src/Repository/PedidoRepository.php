<?php

namespace App\Repository;

use App\Entity\Cliente; // Importar
use App\Entity\Pedido;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Pedido>
 * ...
 */
class PedidoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Pedido::class);
    }

    /**
     * Busca o último pedido PENDENTE de um cliente.
     * Esta é a nossa implementação de "Carrinho Ativo".
     */
    public function findPendentePorCliente(Cliente $cliente): ?Pedido
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.cliente = :cliente')
            ->andWhere('p.status = :status')
            ->setParameter('cliente', $cliente)
            ->setParameter('status', 'PENDENTE')
            ->orderBy('p.dataCriacao', 'DESC') // Pega o mais recente
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult(); // Retorna 1 Pedido ou null
    }

    /**
     * Lista pedidos pendentes expirados (expiraEm < now).
     * Usado pelo comando de liberação de reservas.
     * @return Pedido[]
     */
    public function findExpiredPending(): array
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.status = :status')
            ->andWhere('p.expiraEm IS NOT NULL')
            ->andWhere('p.expiraEm < :agora')
            ->setParameter('status', 'PENDENTE')
            ->setParameter('agora', new \DateTimeImmutable())
            ->orderBy('p.expiraEm', 'ASC')
            ->getQuery()
            ->getResult();
    }
}