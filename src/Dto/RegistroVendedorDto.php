<?php

namespace App\Dto;

class RegistroVendedorDto
{
    public string $email;
    public string $nomeFantasia;
    public string $documento; // apenas dígitos
    public string $tipoDocumento; // 'cpf' | 'cnpj'
}
