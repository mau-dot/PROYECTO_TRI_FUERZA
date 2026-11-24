<?php

namespace App\Enums;

/**
 * Un periodo cerrado ya no acepta cambios.
 */
enum EstadoPeriodo: string
{
    case Abierto = 'abierto';
    case Cerrado = 'cerrado';

    public function label(): string
    {
        return match ($this) {
            self::Abierto => 'Abierto',
            self::Cerrado => 'Cerrado',
        };
    }

    /** Color de Bootstrap para la insignia */
    public function color(): string
    {
        return match ($this) {
            self::Abierto => 'success',
            self::Cerrado => 'secondary',
        };
    }
}