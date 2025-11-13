<?php

namespace App\Enum;

enum EventoStatus: string
{
    case RASCUNHO = 'RASCUNHO';
    case PUBLICADO = 'PUBLICADO';
    case DESATIVADO = 'DESATIVADO';
}
