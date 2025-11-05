<?php

namespace App\Entity;

use App\Repository\EventoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EventoRepository::class)]
class Evento
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nome = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $descricao = null;

    #[ORM\Column(length: 255)]
    private ?string $local = null;

    // Nota: O plano original mencionava 'dataEvento', mas o código usa 'dataHoraInicio' e 'dataHoraFim'.
    // Manteremos os campos existentes. O EventoFormType (próxima etapa) deverá refleti-los.
    #[ORM\Column]
    private ?\DateTime $dataHoraInicio = null;

    #[ORM\Column]
    private ?\DateTime $dataHoraFim = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $capacidadeTotal = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $urlBanner = null;

    /**
     * @var Collection<int, Lote>
     */
    #[ORM\OneToMany(targetEntity: Lote::class, mappedBy: 'evento', orphanRemoval: true, cascade: ['persist'])]
    private Collection $lotes;

    #[ORM\ManyToOne(inversedBy: 'eventos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Vendedor $vendedor = null;

    /**
     * Construtor da entidade Evento.
     * Inicializa coleções e aplica regras de negócio de estado padrão.
     */
    public function __construct()
    {
        $this->lotes = new ArrayCollection();

        // Tarefa 1: Aplicar a regra de negócio de status inicial.
        $this->status = 'RASCUNHO';
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

    public function getDescricao(): ?string
    {
        return $this->descricao;
    }

    public function setDescricao(?string $descricao): static
    {
        $this->descricao = $descricao;

        return $this;
    }

    public function getLocal(): ?string
    {
        return $this->local;
    }

    public function setLocal(string $local): static
    {
        $this->local = $local;

        return $this;
    }

    public function getDataHoraInicio(): ?\DateTime
    {
        return $this->dataHoraInicio;
    }

    public function setDataHoraInicio(\DateTime $dataHoraInicio): static
    {
        $this->dataHoraInicio = $dataHoraInicio;

        return $this;
    }

    public function getDataHoraFim(): ?\DateTime
    {
        return $this->dataHoraFim;
    }

    public function setDataHoraFim(\DateTime $dataHoraFim): static
    {
        $this->dataHoraFim = $dataHoraFim;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        // Futuramente, este 'setter' pode ser privatizado ou conter
        // lógica de transição de estado (State Pattern),
        // mas por enquanto, o construtor resolve o estado inicial.
        $this->status = $status;

        return $this;
    }

    public function getCapacidadeTotal(): ?int
    {
        return $this->capacidadeTotal;
    }

    public function setCapacidadeTotal(int $capacidadeTotal): static
    {
        $this->capacidadeTotal = $capacidadeTotal;

        return $this;
    }

    public function getUrlBanner(): ?string
    {
        return $this->urlBanner;
    }

    public function setUrlBanner(?string $urlBanner): static
    {
        $this->urlBanner = $urlBanner;

        return $this;
    }

    /**
     * @return Collection<int, Lote>
     */
    public function getLotes(): Collection
    {
        return $this->lotes;
    }

    public function addLote(Lote $lote): static
    {
        if (!$this->lotes->contains($lote)) {
            $this->lotes->add($lote);
            $lote->setEvento($this);
        }

        return $this;
    }

    public function removeLote(Lote $lote): static
    {
        if ($this->lotes->removeElement($lote)) {
            // set the owning side to null (unless already changed)
            if ($lote->getEvento() === $this) {
                $lote->setEvento(null);
            }
        }

        return $this;
    }

    public function getVendedor(): ?Vendedor
    {
        return $this->vendedor;
    }

    public function setVendedor(?Vendedor $vendedor): static
    {
        $this->vendedor = $vendedor;

        return $this;
    }
}