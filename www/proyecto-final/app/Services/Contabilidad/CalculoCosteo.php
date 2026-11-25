<?php

namespace App\Services\Contabilidad;

use App\Enums\Comportamiento;
use App\Enums\ElementoCosto;
use App\Models\Costeo;
use App\Support\Dinero;

/**
 *calculos del sistema de costos, en CENTAVOS (igual que los reportes).
 *
 *   monto del elemento       = cantidad anual × costo unitario
 *   costo total anual        = suma de los elementos  = costos fijos + costos variables
 *   costo por unidad         = costo total anual ÷ volumen anual
 *   costo variable unitario  = costos variables ÷ volumen anual
 *
 *el costo total anual es el "costo de operación" que la fase 6 pasa a los flujos de caja.
 */
class CalculoCosteo
{
    /** cantidad × costo unitario, redondeado al centavo. 208 × 2.15 = 44720 centavos */
    public static function monto(mixed $cantidad, mixed $costoUnitario): int
    {
        $centesimas = Dinero::centavos($cantidad);       // 208.00 -> 20800 (centesimas de unidad)
        $centavos = Dinero::centavos($costoUnitario);    // 2.15   -> 215

        return self::dividir($centesimas * $centavos, 100);
    }

    /** division entera con redondeo (la mitad sube), para no perder centavos */
    public static function dividir(int $a, int $b): int
    {
        if ($b === 0) {
            return 0;
        }

        return (int) round($a / $b, 0, PHP_ROUND_HALF_UP);
    }

    public static function resumen(Costeo $costeo): array
    {
        $porElemento = array_fill_keys(array_map(fn ($e) => $e->value, ElementoCosto::cases()), 0);
        $fijo = 0;
        $variable = 0;

        foreach ($costeo->elementos as $e) {
            $monto = $e->monto();
            $porElemento[$e->elemento->value] += $monto;
            if ($e->comportamiento === Comportamiento::Fijo) {
                $fijo += $monto;
            } else {
                $variable += $monto;
            }
        }

        $total = $fijo + $variable;
        $volumen = Dinero::centavos($costeo->volumen_anual);   // en centesimas de unidad

        return [
            'por_elemento' => $porElemento,
            'fijo' => $fijo,
            'variable' => $variable,
            'total' => $total,
            'unitario' => self::dividir($total * 100, $volumen),
            'variable_unitario' => self::dividir($variable * 100, $volumen),
            'estimados' => $costeo->elementos->where('estimado', true)->count(),
            'elementos' => $costeo->elementos->count(),
        ];
    }
}