<?php

namespace App\Service;

use App\Entity\Evento;
use App\Entity\Vendedor;
use App\Repository\EventoRepository;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Camada de Serviço (Lógica de Negócio) para operações de Evento.
 * SRP: Responsável por orquestrar a busca e manipulação de Eventos.
 * @author Jonathan Bufon
 */
class EventoService
{
    public function __construct(
        private readonly EventoRepository $eventoRepository,
        private readonly EntityManagerInterface $em
    ) {
    }

    /**
     * Lógica de negócio para criar um novo evento.
     */
    public function criarNovoEvento(Evento $evento, Vendedor $vendedor): void
    {
        $evento->setVendedor($vendedor);

        // Regra de Negócio: O status 'RASCUNHO' já foi aplicado 
        // pela Entidade Evento em seu construtor.

        $this->em->persist($evento);
        $this->em->flush();
    }

    /**
     * Lógica de negócio para atualizar um evento.
     */
    public function atualizarEvento(Evento $evento): void
    {
        // A entidade $evento já está sendo gerenciada pelo EntityManager
        $this->em->flush();
    }

    /**
     * TAREFA 1: Lógica de negócio para publicar um evento.
     * Contém as regras de transição de estado.
     *
     * @throws \LogicException Se as regras de negócio não forem atendidas.
     */
    public function publicarEvento(Evento $evento): void
    {
        // Regra de Negócio 1: Só é possível publicar eventos em RASCUNHO.
        if ($evento->getStatus() !== 'RASCUNHO') {
            throw new \LogicException('Este evento não pode ser publicado, pois não está em modo Rascunho.');
        }

        // Regra de Negócio 2: Não publicar eventos sem lotes.
        if ($evento->getLotes()->isEmpty()) {
            throw new \LogicException('Este evento não pode ser publicado, pois não possui lotes de ingressos cadastrados.');
        }

        // Transição de Estado
        $evento->setStatus('PUBLICADO');

        // Persistência
        $this->em->flush();
    }


    /**
     * Busca todos os eventos visíveis para o cliente.
     * Regra de Negócio: Apenas eventos 'PUBLICADO' são listados.
     * Regra de Negócio: Eventos são ordenados pela data de início (mais próximos primeiro).
     */
    public function getEventosPublicados(): array
    {
        return $this->eventoRepository->findBy(
            ['status' => 'PUBLICADO'],
            ['dataHoraInicio' => 'ASC']
        );
    }

    /**
     * Busca um evento específico por ID, mas apenas se ele estiver publicado.
     * Regra de Negócio: Um cliente não pode visualizar
     * eventos em 'RASCUNHO' ou 'CANCELADO' pela página pública.
     */
    public function findEventoPublicado(int $id): ?Evento
    {
        return $this->eventoRepository->findOneBy([
            'id' => $id,
            'status' => 'PUBLICADO'
        ]);
    }

    /**
     * Busca os eventos para o Dashboard do Vendedor.
     * Regra de Negócio: Ordena os eventos pela data
     * (ex: os próximos a acontecer primeiro).
     * @author Jonathan Bufon
     */
    public function getEventosPorVendedor(Vendedor $vendedor): array
    {
        return $this->eventoRepository->findBy(
            ['vendedor' => $vendedor],
            ['dataHoraInicio' => 'ASC']
        );
    }
}