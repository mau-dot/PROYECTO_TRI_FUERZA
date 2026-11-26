<?php

namespace App\Services\Economia;

use App\Models\Alternativa;

/**
 * Genera los flujos de caja antes de impuestos.
 */
class FlujosCaja
{
    public static function generar(Alternativa $a): array
    {
        $beneficio = (float) $a->beneficio_anual;
        $costo = $a->costoOperacion();
        $n = $a->vida_util;

        $filas = [[
            'anio' => 0,
            'inversion' => -(float) $a->inversion,
            'beneficio' => 0.0,
            'costo' => 0.0,
            'salvamento' => 0.0,
            'neto' => -(float) $a->inversion,
        ]];

        for ($t = 1; $t <= $n; $t++) {
            $salvamento = $t === $n
                ? (float) $a->valor_salvamento
                : 0.0;

            $filas[] = [
                'anio' => $t,
                'inversion' => 0.0,
                'beneficio' => $beneficio,
                'costo' => -$costo,
                'salvamento' => $salvamento,
                'neto' => round(
                    $beneficio - $costo + $salvamento,
                    2
                ),
            ];
        }

        return $filas;
    }
}