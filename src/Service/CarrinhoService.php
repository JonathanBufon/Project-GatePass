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
     *
     * @throws \LogicException Se o lote não for encontrado ou não tiver estoque.
     */
    public function add(int $loteId, int $quantidade): void
    {
        // --- INÍCIO DA CORREÇÃO ---

        // 1. Validar a Quantidade Mínima
        if ($quantidade < 1) {
            throw new \LogicException('A quantidade deve ser pelo menos 1.');
        }

        // 2. Validar a Existência do Lote (Regra de Negócio)
        $lote = $this->loteRepository->find($loteId);
        if (!$lote) {
            throw new \LogicException('Lote de ingresso não encontrado.');
        }

        // 3. Validar o Estoque (Regra de Negócio)
        // (Assume que Lote::getQuantidadeVendida() está implementado)
        $estoqueDisponivel = $lote->getQuantidadeTotal() - $lote->getQuantidadeVendida();

        // 4. Obtém o carrinho atual da sessão para verificar o que já está lá
        $carrinho = $this->session->get(self::CARRINHO_SESSION_KEY, []);
        $quantidadeJaNoCarrinho = $carrinho[$loteId] ?? 0;
        $quantidadeDesejada = $quantidadeJaNoCarrinho + $quantidade;

        if ($quantidadeDesejada > $estoqueDisponivel) {
            throw new \LogicException(sprintf(
                'Estoque insuficiente para o lote "%s". Disponível: %d, Solicitado: %d',
                $lote->getNome(),
                $estoqueDisponivel,
                $quantidadeDesejada
            ));
        }

        // --- FIM DA CORREÇÃO ---

        // 5. Lógica de adição/atualização
        $carrinho[$loteId] = $quantidadeDesejada;

        // 6. Salva o carrinho de volta na sessão.
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

            // Segurança: Se um ID inválido estiver na sessão, mas não no BD, pule.
            if (!isset($carrinho[$loteId])) {
                continue;
            }

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