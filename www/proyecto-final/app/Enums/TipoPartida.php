<?php

namespace App\Enums;

/**
 * La hoja de trabajo separa las partidas de AJUSTE en sus propias columnas.
 */
enum TipoPartida: string
{
    case Apertura = 'apertura';
    case Diario = 'diario';
    case Ajuste = 'ajuste';
    case Cierre = 'cierre';

    public function label(): string
    {
        return match ($this) {
            self::Apertura => 'Apertura',
            self::Diario => 'Diario',
            self::Ajuste => 'Ajuste',
            self::Cierre => 'Cierre',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Apertura => 'info',
            self::Diario => 'secondary',
            self::Ajuste => 'warning',
            self::Cierre => 'dark',
        };
    }
}