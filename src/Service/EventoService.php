<?php

namespace App\Service;

use App\Repository\EventoRepository;

/**
 * Camada de Serviço (Lógica de Negócio) para operações de Evento.
 * SRP: Responsável por orquestrar a busca e manipulação de Eventos.
 * @author Jonathan Bufon
 */
class EventoService
{
    public function __construct(private readonly EventoRepository $eventoRepository)
    {
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
        // O Serviço delega a busca de dados ao Repositório,
        // aplicando a regra de negócio (filtros e ordenação).
        return $this->eventoRepository->findBy(
            ['status' => 'PUBLICADO'],           // Critério (WHERE status = 'PUBLICADO')
            ['dataHoraInicio' => 'ASC']      // Ordenação (ORDER BY dataHoraInicio ASC)
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
     * Regra de Negócio: Ordena os eventos pela data
     * (ex: os próximos a acontecer primeiro).
     * @author Jonathan Bufon
     */
    public function getEventosPorVendedor(Vendedor $vendedor): array
    {
        // O Serviço delega ao Repositório
        // e aplica a regra de negócio (ordenação).
        return $this->eventoRepository->findByVendedor(
            $vendedor,
            ['dataHoraInicio' => 'ASC'] // Ordena por data
        );
    }

}