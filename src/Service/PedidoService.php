<?php

namespace App\Service;

use App\Entity\Ingresso;
use App\Entity\Lote;
use App\Entity\Pedido;
use App\Entity\Usuario;
use App\Repository\PedidoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Camada de Serviço (Lógica de Negócio) para o domínio de Vendas.
 * Responsável por orquestrar a criação e modificação de Pedidos.
 * @author Jonathan Bufon
 */
class PedidoService
{
    public function __construct(
        // DIP: Dependemos de abstrações
        private readonly EntityManagerInterface $em,
        private readonly PedidoRepository $pedidoRepository
    ) {
    }

    /**
     * Orquestra a adição de um Lote (produto) a um Pedido (carrinho).
     *
     * @throws AccessDeniedException Se o Usuário não for um Cliente.
     * @throws \Exception Regras de negócio (ex: estoque esgotado).
     */
    public function adicionarLoteAoPedido(Lote $lote, Usuario $usuario): Pedido
    {
        // Regra de Negócio: Verifica se o Usuário é um Cliente
        $cliente = $usuario->getCliente();
        if (!$cliente) {
            throw new AccessDeniedException('Ação permitida apenas para clientes.');
        }

        // Regra de Negócio: (Futura) Verificar se o Lote tem estoque
        // if ($lote->getEstoqueDisponivel() <= 0) { ... }

        // Regra de Negócio: Encontrar o "Carrinho" (Pedido PENDENTE)
        // (Delegamos ao Repositório, que criamos no Passo 2)
        $pedido = $this->pedidoRepository->findPendentePorCliente($cliente);

        if (!$pedido) {
            $pedido = new Pedido();
            $pedido->setCliente($cliente);
            $this->em->persist($pedido);
        }

        $ingresso = new Ingresso();
        $ingresso->setLote($lote);
        $ingresso->setValorPago($lote->getPreco()); // Preço no momento da compra
        $ingresso->setStatus('RESERVADO'); // Status até o pagamento
        $ingresso->setCodigoUnico(uniqid('ING-') . bin2hex(random_bytes(5))); // Gera um código único

        $pedido->addIngresso($ingresso); // (Este método deve existir na Entidade Pedido)

        $pedido->recalcularTotal();

        // (Não precisamos persistir $ingresso pois 'cascade: persist' em Pedido->ingressos deve cuidar disso)
        // (Se não houver cascade, persistimos manualmente)
        $this->em->persist($ingresso);
        $this->em->persist($pedido); // Atualiza o pedido

        $this->em->flush();

        return $pedido;
    }
}