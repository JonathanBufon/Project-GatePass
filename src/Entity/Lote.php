<?php

namespace App\Entity;

use App\Repository\LoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LoteRepository::class)]
#[ORM\Table(name: 'lote', indexes: [new ORM\Index(columns: ['evento_id'])])]
class Lote
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nome = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $preco = null;

    #[ORM\Column]
    private ?int $quantidadeTotal = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dataInicioVendas = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $dataFimVendas = null;

    #[ORM\ManyToOne(inversedBy: 'lotes')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evento $evento = null;

    /**
     * @var Collection<int, Ingresso>
     */
    #[ORM\OneToMany(targetEntity: Ingresso::class, mappedBy: 'lote')]
    private Collection $ingressos;

    public function __construct()
    {
        $this->ingressos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;

        return $this;
    }

    public function getPreco(): ?string
    {
        return $this->preco;
    }

    public function setPreco(string $preco): static
    {
        $this->preco = $preco;

        return $this;
    }

    public function getQuantidadeTotal(): ?int
    {
        return $this->quantidadeTotal;
    }

    public function setQuantidadeTotal(int $quantidadeTotal): static
    {
        $this->quantidadeTotal = $quantidadeTotal;

        return $this;
    }

    public function getDataInicioVendas(): ?\DateTime
    {
        return $this->dataInicioVendas;
    }

    public function setDataInicioVendas(?\DateTime $dataInicioVendas): static
    {
        $this->dataInicioVendas = $dataInicioVendas;

        return $this;
    }

    public function getDataFimVendas(): ?\DateTime
    {
        return $this->dataFimVendas;
    }

    public function setDataFimVendas(?\DateTime $dataFimVendas): static
    {
        $this->dataFimVendas = $dataFimVendas;

        return $this;
    }

    public function getEvento(): ?Evento
    {
        return $this->evento;
    }

    public function setEvento(?Evento $evento): static
    {
        $this->evento = $evento;

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
            $ingresso->setLote($this);
        }

        return $this;
    }

    public function removeIngresso(Ingresso $ingresso): static
    {
        if ($this->ingressos->removeElement($ingresso)) {
            // set the owning side to null (unless already changed)
            if ($ingresso->getLote() === $this) {
                $ingresso->setLote(null);
            }
        }

        return $this;
    }

    /**
     * TAREFA 1: Método de encapsulamento para a regra de negócio de estoque.
     * Informa quantos ingressos já foram associados (vendidos ou reservados).
     */
    public function getQuantidadeVendida(): int
    {
        return $this->ingressos->count();
    }
}