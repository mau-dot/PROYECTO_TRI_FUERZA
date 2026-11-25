<?php

namespace App\Enums;

/*fijo: no cambia con el volumen. 

variable: sube o baja con el volumen. */
enum Comportamiento: string
{
    case Fijo = 'fijo';
    case Variable = 'variable';

    public function label(): string
    {
        return match ($this) {
            self::Fijo => 'Fijo',
            self::Variable => 'Variable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Fijo => 'secondary',
            self::Variable => 'info',
        };
    }
}
