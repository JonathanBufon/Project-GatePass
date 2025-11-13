<?php

namespace App\Entity;

use App\Repository\SetorRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SetorRepository::class)]
#[ORM\Table(name: 'setor', indexes: [new ORM\Index(columns: ['evento_id'])])]
class Setor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Evento $evento = null;

    #[ORM\Column(length: 120)]
    private ?string $nome = null;

    #[ORM\Column]
    private ?int $capacidade = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getNome(): ?string
    {
        return $this->nome;
    }

    public function setNome(string $nome): static
    {
        $this->nome = $nome;
        return $this;
    }

    public function getCapacidade(): ?int
    {
        return $this->capacidade;
    }

    public function setCapacidade(int $capacidade): static
    {
        $this->capacidade = $capacidade;
        return $this;
    }
}
