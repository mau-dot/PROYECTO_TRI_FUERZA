<?php

namespace App\Services\Economia;

use App\Models\Alternativa;

/**
 * Fase 7: analisis de retiro y reemplazo.
 *
 * Compara el costo anual equivalente del defensor
 * contra el costo anual equivalente del retador.
 *
 * Evaluacion antes de impuestos.
 */
class Reemplazo
{
    /**
     * Evalua la conveniencia de conservar o reemplazar.
     */
    public static function evaluar(
        Alternativa $retador,
        float $i,
        float $valorMercadoDefensor,
        float $costoDefensor,
        int $vidaRestanteDefensor,
        float $perdidaValorDefensor = 0.15,
        float $crecimientoCostoDefensor = 0.08,
        float $perdidaValorRetador = 0.15,
        float $crecimientoCostoRetador = 0.08
    ): array
    {
        // Proyeccion del activo existente.
        // Se utiliza su valor de mercado actual,
        // no su costo historico de adquisicion.
        $proyeccionDefensor = VidaEconomica::proyectar(
            $valorMercadoDefensor,
            $costoDefensor,
            $vidaRestanteDefensor,
            $perdidaValorDefensor,
            $crecimientoCostoDefensor
        );

        // Calcular vida economica del defensor.
        $defensor = VidaEconomica::calcular(
            $valorMercadoDefensor,
            $i,
            $proyeccionDefensor
        );

        // Calcular vida economica del nuevo activo.
        $nuevo = VidaEconomica::evaluar(
            $retador,
            $i,
            $perdidaValorRetador,
            $crecimientoCostoRetador
        );

        // Costos anuales equivalentes.
        $caueDefensor = $defensor['caue_minimo'];

        $caueRetador = $nuevo['caue_minimo'];

        // Ahorro anual equivalente al reemplazar.
        $ahorroAnual = $caueDefensor - $caueRetador;

        // Porcentaje de ahorro respecto al defensor.
        $porcentajeAhorro = $caueDefensor > 0
            ? ($ahorroAnual / $caueDefensor) * 100
            : null;

        // Criterio de decision.
        // Tolerancia monetaria de un centavo.
        if (abs($ahorroAnual) < 0.01) {

            $decision = 'indiferente';

            $mensaje =
                'Ambos activos tienen costos anuales equivalentes.';

        } elseif ($ahorroAnual > 0) {

            $decision = 'reemplazar';

            $mensaje =
                'Conviene reemplazar el activo actual por el retador, '
                .'porque presenta un menor costo anual equivalente.';

        } else {

            $decision = 'conservar';

            $mensaje =
                'Conviene conservar el activo actual, '
                .'porque presenta un menor costo anual equivalente.';
        }

        return [
            'tmar' => $i,

            'defensor' => array_merge($defensor, [
                'nombre' => 'Activo actual',

                'valor_mercado_actual' =>
                    $valorMercadoDefensor,

                'costo_inicial' =>
                    $costoDefensor,

                'vida_restante' =>
                    $vidaRestanteDefensor,

                'perdida_valor' =>
                    $perdidaValorDefensor,

                'crecimiento_costo' =>
                    $crecimientoCostoDefensor,
            ]),

            'retador' => $nuevo,

            'caue_defensor' => $caueDefensor,

            'caue_retador' => $caueRetador,

            'ahorro_anual' => $ahorroAnual,

            'porcentaje_ahorro' => $porcentajeAhorro,

            'decision' => $decision,

            'mensaje' => $mensaje,

            // Los valores futuros son proyecciones.
            'provisional' => true,
        ];
    }
}
