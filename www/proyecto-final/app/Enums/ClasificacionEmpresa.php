<?php

namespace App\Enums;

/**
 * Clasificacion de la empresa segun su actividad 
 * 
 */
enum ClasificacionEmpresa: string
{
    case Comercial = 'comercial';
    case Industrial = 'industrial';
    case Servicios = 'servicios';

    public function label(): string
    {
        return match ($this) {
            self::Comercial => 'Comercial (compra y venta de mercadería)',
            self::Industrial => 'Industrial (transforma materia prima)',
            self::Servicios => 'De servicios',
        };
    }
}