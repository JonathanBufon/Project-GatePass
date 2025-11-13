<?php

namespace App\Entity;

use App\Repository\IngressoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IngressoRepository::class)]
class Ingresso
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255, unique: true)]
    private ?string $codigoUnico = null;

    #[ORM\Column(length: 36, unique: true, nullable: true)]
    private ?string $identificadorUnico = null;

    #[ORM\Column(length: 50)]
    private ?string $status = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $valorPago = null;

    #[ORM\ManyToOne(inversedBy: 'ingressos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Pedido $pedido = null;

    #[ORM\ManyToOne(inversedBy: 'ingressos')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Lote $lote = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $filePath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCodigoUnico(): ?string
    {
        return $this->codigoUnico;
    }

    public function setCodigoUnico(string $codigoUnico): static
    {
        $this->codigoUnico = $codigoUnico;

        return $this;
    }

    public function getIdentificadorUnico(): ?string
    {
        return $this->identificadorUnico;
    }

    public function setIdentificadorUnico(?string $identificadorUnico): static
    {
        $this->identificadorUnico = $identificadorUnico;

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

    public function getValorPago(): ?string
    {
        return $this->valorPago;
    }

    public function setValorPago(string $valorPago): static
    {
        $this->valorPago = $valorPago;

        return $this;
    }

    public function getPedido(): ?Pedido
    {
        return $this->pedido;
    }

    public function setPedido(?Pedido $pedido): static
    {
        $this->pedido = $pedido;

        return $this;
    }

    public function getLote(): ?Lote
    {
        return $this->lote;
    }

    public function setLote(?Lote $lote): static
    {
        $this->lote = $lote;

        return $this;
    }

    public function getFilePath(): ?string
    {
        return $this->filePath;
    }

    public function setFilePath(?string $filePath): static
    {
        $this->filePath = $filePath;

        return $this;
    }
}
