<?php

namespace App\Entity;

use App\Repository\VendedorRepository;
use App\Enum\TipoDocumento;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: VendedorRepository::class)]
#[UniqueEntity(fields: ['documento'], message: 'Já existe um vendedor com este documento.')]
class Vendedor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nomeFantasia = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $documento = null; // apenas dígitos (CPF ou CNPJ)

    #[ORM\Column(enumType: TipoDocumento::class)]
    private ?TipoDocumento $tipoDocumento = null; // 'cpf' ou 'cnpj'

    #[ORM\OneToOne(inversedBy: 'vendedor', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Usuario $usuario = null;

    /**
     * @var Collection<int, Evento>
     */
    #[ORM\OneToMany(targetEntity: Evento::class, mappedBy: 'vendedor')]
    private Collection $eventos;

    public function __construct()
    {
        $this->eventos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomeFantasia(): ?string
    {
        return $this->nomeFantasia;
    }

    public function setNomeFantasia(string $nomeFantasia): static
    {
        $this->nomeFantasia = $nomeFantasia;

        return $this;
    }

    public function getDocumento(): ?string
    {
        return $this->documento;
    }

    public function setDocumento(string $documento): static
    {
        $this->documento = $documento;

        return $this;
    }

    public function getTipoDocumento(): ?TipoDocumento
    {
        return $this->tipoDocumento;
    }

    public function setTipoDocumento(TipoDocumento $tipoDocumento): static
    {
        $this->tipoDocumento = $tipoDocumento;

        return $this;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario): static
    {
        $this->usuario = $usuario;

        return $this;
    }

    /**
     * @return Collection<int, Evento>
     */
    public function getEventos(): Collection
    {
        return $this->eventos;
    }

    public function addEvento(Evento $evento): static
    {
        if (!$this->eventos->contains($evento)) {
            $this->eventos->add($evento);
            $evento->setVendedor($this);
        }

        return $this;
    }

    public function removeEvento(Evento $evento): static
    {
        if ($this->eventos->removeElement($evento)) {
            // set the owning side to null (unless already changed)
            if ($evento->getVendedor() === $this) {
                $evento->setVendedor(null);
            }
        }

        return $this;
    }
}
