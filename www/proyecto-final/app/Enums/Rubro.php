<?php

namespace App\Enums;

/** Para ordenar el Balance General (solo activos y pasivos) */
enum Rubro: string
{
    case Corriente = 'corriente';
    case NoCorriente = 'no_corriente';

    public function label(): string
    {
        return match ($this) {
            self::Corriente => 'Corriente',
            self::NoCorriente => 'No corriente',
        };
    }
}