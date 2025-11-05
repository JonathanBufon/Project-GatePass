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
     *
     * @param Evento $evento A entidade Evento (DTO preenchido pelo formulário).
     * @param Vendedor $vendedor O Vendedor autenticado (dono do evento).
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
     *
     * @param Evento $evento A entidade Evento já modificada pelo formulário (DTO).
     */
    public function atualizarEvento(Evento $evento): void
    {
        // O Symfony Form (DTO) já hidratou a entidade $evento
        // com os novos dados vindos da Request.

        // Lógica de Negócio (Exemplo futuro): [cite: 1710]
        // if ($evento->getStatus() === 'PUBLICADO' && $evento->getCapacidadeTotal() < $evento->getCapacidadeAtual()) {
        //     throw new \Exception('Não é possível reduzir a capacidade abaixo das vendas atuais.');
        // }

        // Apenas chamamos flush(), pois a entidade $evento já está
        // sendo gerenciada pelo EntityManager (foi buscada pelo ParamConverter).
        $this->em->flush();
    }

    /**
     * Busca todos os eventos visíveis para o cliente.
     *
     * Regra de Negócio: Apenas eventos 'PUBLICADO' são listados.
     * Regra de Negócio: Eventos são ordenados pela data de início (mais próximos primeiro).
     *
     * @return array Um array de entidades Evento.
     */
    public function getEventosPublicados(): array
    {
        return $this->eventoRepository->findBy(
            ['status' => 'PUBLICADO'],
            ['dataHoraInicio' => 'ASC']
        );
    }

    /**
     * Busca um evento específico por ID,
     * mas apenas se ele estiver publicado.
     *
     * Regra de Negócio: Um cliente não pode visualizar
     * eventos em 'RASCUNHO' ou 'CANCELADO' pela página pública.
     *
     * @return Evento|null Retorna a entidade ou null se não encontrar
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
     * Regra de NegGócio: Ordena os eventos pela data
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