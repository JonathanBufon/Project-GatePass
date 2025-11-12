<?php

namespace App\Enum;

enum TipoDocumento: string
{
    case CPF = 'cpf';
    case CNPJ = 'cnpj';
}
