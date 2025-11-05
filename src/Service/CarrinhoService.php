<?php

namespace App\Service;

use App\Entity\Lote;
use App\Repository\LoteRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Camada de Serviço (Lógica de Negócio) para o Carrinho de Compras.
 * SRP: Responsável por gerenciar o estado do carrinho (armazenado na Sessão).
 * Não interage diretamente com o EntityManager para persistência.
 */
class CarrinhoService
{
    private const CARRINHO_SESSION_KEY = 'gatepass_carrinho';

    private SessionInterface $session;

    public function __construct(
        RequestStack $requestStack,
        private readonly LoteRepository $loteRepository
    ) {
        $this->session = $requestStack->getSession();
    }

    /**
     * Adiciona um item (Lote) e quantidade ao carrinho na sessão.
     *
     * @param int $loteId O ID do Lote a ser adicionado.
     * @param int $quantidade A quantidade de ingressos.
     */
    public function add(int $loteId, int $quantidade): void
    {
        // 1. Obtém o carrinho atual da sessão, ou um array vazio.
        $carrinho = $this->session->get(self::CARRINHO_SESSION_KEY, []);

        // 2. Lógica de adição/atualização
        // (Se já existir, soma a quantidade; senão, define a nova)
        if (isset($carrinho[$loteId])) {
            $carrinho[$loteId] += $quantidade;
        } else {
            $carrinho[$loteId] = $quantidade;
        }

        // (Opcional: Validação de quantidade máxima, etc.)

        // 3. Salva o carrinho de volta na sessão.
        $this->session->set(self::CARRINHO_SESSION_KEY, $carrinho);
    }

    /**
     * Limpa o carrinho (remove da sessão).
     * Será usado após o Pedido ser finalizado.
     */
    public function limpar(): void
    {
        $this->session->remove(self::CARRINHO_SESSION_KEY);
    }

    /**
     * Busca os itens do carrinho (IDs e Qtd) e "hidrata"
     * com os dados do LoteRepository (preço, nome, etc.)
     *
     * @return array Retorna [ 'itens' => [], 'total' => 0.0 ]
     */
    public function getItensDetalhado(): array
    {
        $carrinho = $this->session->get(self::CARRINHO_SESSION_KEY, []);

        if (empty($carrinho)) {
            return ['itens' => [], 'total' => 0.0];
        }

        // 1. Pega os IDs dos lotes que estão no carrinho
        $loteIds = array_keys($carrinho);

        // 2. Busca as *entidades* Lote no banco de dados
        $lotes = $this->loteRepository->findBy(['id' => $loteIds]);

        $itensDetalhados = [];
        $totalCompra = 0.0;

        // 3. Monta a estrutura de retorno
        foreach ($lotes as $lote) {
            $loteId = $lote->getId();
            $quantidade = $carrinho[$loteId];
            $precoUnitario = (float) $lote->getPreco();
            $subtotal = $precoUnitario * $quantidade;

            $itensDetalhados[] = [
                'lote' => $lote,
                'quantidade' => $quantidade,
                'subtotal' => $subtotal
            ];

            $totalCompra += $subtotal;
        }

        return [
            'itens' => $itensDetalhados,
            'total' => $totalCompra
        ];
    }

    /**
     * Retorna a contagem de itens únicos no carrinho.
     * (Útil para o ícone do carrinho na navbar)
     */
    public function getContagemItens(): int
    {
        $carrinho = $this->session->get(self::CARRINHO_SESSION_KEY, []);
        return count($carrinho);
    }
}