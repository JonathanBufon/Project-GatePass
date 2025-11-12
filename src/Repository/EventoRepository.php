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

    /**
     * Lista eventos publicados com seleção parcial de campos para a listagem pública (Album).
     */
    public function findPublishedList(): array
    {
        return $this->createQueryBuilder('e')
            ->select('partial e.{id,nome,local,dataHoraInicio,status,urlBanner}')
            ->where('e.status = :status')
            ->setParameter('status', 'PUBLICADO')
            ->orderBy('e.dataHoraInicio', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Busca um evento publicado por id com seleção parcial adequada ao detalhe.
     */
    public function findOnePublishedById(int $id): ?Evento
    {
        return $this->createQueryBuilder('e')
            ->where('e.id = :id')
            ->andWhere('e.status = :status')
            ->setParameter('id', $id)
            ->setParameter('status', 'PUBLICADO')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Eventos em destaque: publicados, com banner e data futura.
     */
    public function findFeatured(int $limite = 4): array
    {
        $qb = $this->createQueryBuilder('e')
            ->where('e.status = :status')
            ->andWhere('e.urlBanner IS NOT NULL')
            ->andWhere('e.dataHoraInicio > :agora')
            ->setParameter('status', 'PUBLICADO')
            ->setParameter('agora', new \DateTime())
            ->orderBy('e.dataHoraInicio', 'ASC')
            ->setMaxResults($limite);

        return $qb->getQuery()->getResult();
    }

    /**
     * Banners da Home: reusa eventos com banner (pode ser substituído por entidade Banner futuramente).
     */
    public function findHomeBanners(int $limite = 3): array
    {
        return $this->createQueryBuilder('e')
            ->select('partial e.{id,nome,descricao,urlBanner}')
            ->where('e.status = :status')
            ->andWhere('e.urlBanner IS NOT NULL')
            ->setParameter('status', 'PUBLICADO')
            ->orderBy('e.dataHoraInicio', 'ASC')
            ->setMaxResults($limite)
            ->getQuery()
            ->getResult();
    }
}