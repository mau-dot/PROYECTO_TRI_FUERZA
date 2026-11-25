<?php

namespace App\Enums;

/*los elementos del costo. ejm : en una tienda la "materia prima" es la mercaderia*/
enum ElementoCosto: string
{
    case Mpd = 'mpd';
    case Mod = 'mod';
    case Cif = 'cif';
    case GastoOperacion = 'gasto_operacion';

    public function label(): string
    {
        return match ($this) {
            self::Mpd => 'Materia prima / mercadería directa',
            self::Mod => 'Mano de obra directa',
            self::Cif => 'Costos indirectos',
            self::GastoOperacion => 'Gastos de operación',
        };
    }

    public function sigla(): string
    {
        return match ($this) {
            self::Mpd => 'MPD',
            self::Mod => 'MOD',
            self::Cif => 'CIF',
            self::GastoOperacion => 'GO',
        };
    }
}