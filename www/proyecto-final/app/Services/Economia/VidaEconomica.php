<?php

namespace App\Services\Economia;

use App\Models\Alternativa;
use InvalidArgumentException;

/**
 * Fase 7: determinacion de la vida economica.
 *
 * Calcula el costo anual uniforme equivalente
 * para cada periodo posible de conservacion.
 *
 * Todas las tasas se reciben en decimal.
 */
class VidaEconomica
{
    /**
     * Genera una proyeccion de valores de mercado
     * y costos de operacion.
     */
    public static function proyectar(
        float $inversion,
        float $costoInicial,
        int $vidaUtil,
        float $perdidaValor = 0.15,
        float $crecimientoCosto = 0.08
    ): array
    {
        if (
            $inversion <= 0 ||
            $costoInicial < 0 ||
            $vidaUtil < 1 ||
            $vidaUtil > 100
        ) {
            throw new InvalidArgumentException(
                'Los datos del activo no son validos.'
            );
        }

        if (
            $perdidaValor < 0 ||
            $perdidaValor > 1 ||
            $crecimientoCosto < 0 ||
            $crecimientoCosto > 1
        ) {
            throw new InvalidArgumentException(
                'Las tasas deben estar entre 0 y 100 %.'
            );
        }

        $filas = [];

        for ($n = 1; $n <= $vidaUtil; $n++) {

            // Valor de mercado al final del año.
            $valorMercado =
                $inversion * (1 - $perdidaValor) ** $n;

            // El primer año conserva el costo inicial.
            $costoOperacion =
                $costoInicial
                * (1 + $crecimientoCosto) ** ($n - 1);

            $filas[] = [
                'anio' => $n,
                'valor_mercado' => $valorMercado,
                'costo_operacion' => $costoOperacion,
            ];
        }

        return $filas;
    }

    /**
     * Calcula el CAUE para cada año posible.
     */
    public static function calcular(
        float $inversion,
        float $i,
        array $proyeccion
    ): array
    {
        if (
            $inversion <= 0 ||
            $i <= 0 ||
            $i > 1 ||
            empty($proyeccion)
        ) {
            throw new InvalidArgumentException(
                'Parametros de evaluacion no validos.'
            );
        }

        $filas = [];

        $vpCostosAcumulados = 0.0;

        $minimo = null;

        foreach ($proyeccion as $k => $dato) {

            $n = (int) $dato['anio'];

            $vm = (float) $dato['valor_mercado'];

            $co = (float) $dato['costo_operacion'];

            if (
                $n !== $k + 1 ||
                $vm < 0 ||
                $co < 0
            ) {
                throw new InvalidArgumentException(
                    'La proyeccion debe contener años consecutivos y valores no negativos.'
                );
            }

            // Factor de valor presente.
            $pf = Factores::pf($i, $n);

            // Factor de recuperacion de capital.
            $ap = Factores::ap($i, $n);

            // Valor presente del costo del año.
            $vpCostoAnual = $co * $pf;

            // Suma de los costos descontados.
            $vpCostosAcumulados += $vpCostoAnual;

            // Valor presente del mercado.
            $vpMercado = $vm * $pf;

            // Valor presente de los costos totales.
            $vpTotal =
                $inversion
                - $vpMercado
                + $vpCostosAcumulados;

            // Recuperacion anual de capital.
            $caueCapital =
                ($inversion - $vpMercado) * $ap;

            // Costo anual equivalente de operacion.
            $caueOperacion =
                $vpCostosAcumulados * $ap;

            // Costo anual uniforme equivalente total.
            $caueTotal = $vpTotal * $ap;

            $fila = [
                'anio' => $n,

                'valor_mercado' => $vm,
                'costo_operacion' => $co,

                'factor_pf' => $pf,
                'factor_ap' => $ap,

                'vp_costo_anual' => $vpCostoAnual,

                'vp_costos_acumulados' =>
                    $vpCostosAcumulados,

                'vp_mercado' => $vpMercado,
                'vp_total' => $vpTotal,

                'caue_capital' => $caueCapital,
                'caue_operacion' => $caueOperacion,

                'caue_total' => $caueTotal,
            ];

            $filas[] = $fila;

            // Identificar el año con menor CAUE.
            if (
                $minimo === null ||
                $caueTotal < $minimo['caue_total']
            ) {
                $minimo = $fila;
            }
        }

        // Marcar el año economicamente optimo.
        foreach ($filas as &$fila) {

            $fila['optimo'] =
                $fila['anio'] === $minimo['anio'];
        }

        unset($fila);

        return [
            'inversion' => $inversion,
            'tmar' => $i,

            'vida_economica' => $minimo['anio'],

            'caue_minimo' => $minimo['caue_total'],

            'valor_mercado_optimo' =>
                $minimo['valor_mercado'],

            'costo_operacion_optimo' =>
                $minimo['costo_operacion'],

            'filas' => $filas,
        ];
    }

    /**
     * Evalua una alternativa registrada en Economia.
     */
    public static function evaluar(
        Alternativa $a,
        float $i,
        float $perdidaValor = 0.15,
        float $crecimientoCosto = 0.08
    ): array
    {
        $p = (float) $a->inversion;

        $co = $a->costoOperacion();

        $n = (int) $a->vida_util;

        $proyeccion = self::proyectar(
            $p,
            $co,
            $n,
            $perdidaValor,
            $crecimientoCosto
        );

        $resultado = self::calcular(
            $p,
            $i,
            $proyeccion
        );

        return array_merge($resultado, [
            'alternativa_id' => $a->id,
            'alternativa' => $a->nombre,

            'vida_util' => $n,

            'costo_inicial' => $co,

            'perdida_valor' => $perdidaValor,

            'crecimiento_costo' => $crecimientoCosto,

            'origen_costo' => $a->origenCosto(),

            // Proyeccion basada en supuestos.
            'provisional' => true,
        ]);
    }
}
