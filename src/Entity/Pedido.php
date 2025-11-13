<?php

namespace App\Entity;

use App\Repository\PedidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
#[ORM\Table(name: 'pedido')]
class Pedido
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private ?\DateTimeImmutable $dataCriacao = null;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private ?\DateTimeImmutable $expiraEm = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTime $dataPagamento = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valorTotal = null;

    #[ORM\ManyToOne(inversedBy: 'pedidos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Cliente $cliente = null;

    /**
     * @var Collection<int, Ingresso>
     */
    // Isso torna o Pedido um "Aggregate Root". Ao persistir o Pedido,
    // os Ingressos novos são persistidos automaticamente.
    #[ORM\OneToMany(targetEntity: Ingresso::class, mappedBy: 'pedido', cascade: ['persist'])]
    private Collection $ingressos;

    public function __construct()
    {
        $this->ingressos = new ArrayCollection();
        $this->dataCriacao = new \DateTimeImmutable();

        $this->status = 'PENDENTE';
        $this->valorTotal = '0.00';
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDataCriacao(): ?\DateTimeImmutable
    {
        return $this->dataCriacao;
    }

    public function setDataCriacao(\DateTimeImmutable $dataCriacao): static
    {
        $this->dataCriacao = $dataCriacao;

        return $this;
    }

    public function getExpiraEm(): ?\DateTimeImmutable
    {
        return $this->expiraEm;
    }

    public function setExpiraEm(?\DateTimeImmutable $expiraEm): static
    {
        $this->expiraEm = $expiraEm;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getDataPagamento(): ?\DateTime
    {
        return $this->dataPagamento;
    }

    public function setDataPagamento(?\DateTime $dataPagamento): static
    {
        $this->dataPagamento = $dataPagamento;

        return $this;
    }

    public function isExpirado(): bool
    {
        return $this->expiraEm !== null && new \DateTimeImmutable() > $this->expiraEm;
    }

    public function getValorTotal(): ?string
    {
        return $this->valorTotal;
    }

    public function setValorTotal(string $valorTotal): static
    {
        $this->valorTotal = $valorTotal;

        return $this;
    }

    public function getCliente(): ?Cliente
    {
        return $this->cliente;
    }

    public function setCliente(?Cliente $cliente): static
    {
        $this->cliente = $cliente;

        return $this;
    }

    /**
     * @return Collection<int, Ingresso>
     */
    public function getIngressos(): Collection
    {
        return $this->ingressos;
    }

    public function addIngresso(Ingresso $ingresso): static
    {
        if (!$this->ingressos->contains($ingresso)) {
            $this->ingressos->add($ingresso);
            $ingresso->setPedido($this);

            $this->recalcularTotal();
        }

        return $this;
    }

    public function removeIngresso(Ingresso $ingresso): static
    {
        if ($this->ingressos->removeElement($ingresso)) {
            if ($ingresso->getPedido() === $this) {
                $ingresso->setPedido(null);
            }
        }

        // Recalcula o total ao remover
        $this->recalcularTotal();

        return $this;
    }

    /**
     * A entidade Pedido é a especialista em
     * calcular seu próprio total com base em seus ingressos.
     */
    public function recalcularTotal(): void
    {
        $total = 0.00;
        foreach ($this->getIngressos() as $ingresso) {
            // Regra de Negócio: Apenas ingressos não cancelados somam ao total
            if ($ingresso->getStatus() !== 'CANCELADO') {
                // (Garante que o valor pago é tratado como float na soma)
                $total += (float) $ingresso->getValorPago();
            }
        }

        $this->valorTotal = (string) $total;
    }
}