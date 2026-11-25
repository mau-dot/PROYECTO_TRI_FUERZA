<?php

namespace App\Enums;

/**
 * Sistemas de costos que
 * evalua una inversion
 */
enum SistemaCosteo: string
{
    case Estandar = 'estandar';
    case Proceso = 'proceso';
    case Orden = 'orden';

    public function label(): string
    {
        return match ($this) {
            self::Estandar => 'Costos estándar (predeterminados)',
            self::Proceso => 'Costos por proceso',
            self::Orden => 'Costos por órdenes de producción',
        };
    }

    public function descripcion(): string
    {
        return match ($this) {
            self::Estandar => 'Se fija de antemano cuánto debería costar operar cada alternativa (cantidad estándar × precio estándar). Luego se compara con lo real.',
            self::Proceso => 'Para producción continua: los costos se acumulan por proceso o departamento y se dividen entre las unidades producidas.',
            self::Orden => 'Para trabajos por pedido: cada orden acumula su materia prima, mano de obra y costos indirectos.',
        };
    }
}