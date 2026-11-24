<?php

namespace App\Enums;

/**
 * Tamaño de la empresa (clasificacion de CONAMYPE en El Salvador).
 */
enum TamanoEmpresa: string
{
    case Micro = 'micro';
    case Pequena = 'pequena';
    case Mediana = 'mediana';
    case Grande = 'grande';

    public function label(): string
    {
        return match ($this) {
            self::Micro => 'Microempresa',
            self::Pequena => 'Pequeña empresa',
            self::Mediana => 'Mediana empresa',
            self::Grande => 'Gran empresa',
        };
    }
}