<?php

namespace App\Enums;

/**
 * Tipo de cuenta: dice en que estado financiero aparece.
 * Activo, pasivo y patrimonio -> Balance General.
 * Costo, gasto e ingreso -> Estado de Resultados.
 */
enum TipoCuenta: string
{
    case Activo = 'activo';
    case Pasivo = 'pasivo';
    case Patrimonio = 'patrimonio';
    case Costo = 'costo';
    case Gasto = 'gasto';
    case Ingreso = 'ingreso';
    case Liquidadora = 'liquidadora';

    public function label(): string
    {
        return match ($this) {
            self::Activo => 'Activo',
            self::Pasivo => 'Pasivo',
            self::Patrimonio => 'Patrimonio',
            self::Costo => 'Costo',
            self::Gasto => 'Gasto',
            self::Ingreso => 'Ingreso',
            self::Liquidadora => 'Liquidadora',
        };
    }

    /** Naturaleza normal del tipo (se puede cambiar en cuentas especiales, p. ej. depreciación acumulada) */
    public function naturalezaNormal(): Naturaleza
    {
        return match ($this) {
            self::Activo, self::Costo, self::Gasto => Naturaleza::Deudora,
            default => Naturaleza::Acreedora,
        };
    }
}