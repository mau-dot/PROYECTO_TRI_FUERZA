<?php

namespace App\Enums;

/**
 * Deudora: aumenta por el DEBE (activos, costos, gastos).
 * Acreedora: aumenta por el HABER (pasivos, patrimonio, ingresos).
 * Las "contra cuentas" llevan la naturaleza contraria a su grupo
 * (depreciación acumulada es un activo de naturaleza acreedora).
 */
enum Naturaleza: string
{
    case Deudora = 'deudora';
    case Acreedora = 'acreedora';

    public function label(): string
    {
        return match ($this) {
            self::Deudora => 'Deudora',
            self::Acreedora => 'Acreedora',
        };
    }
}