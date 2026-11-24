<?php

namespace App\Services\Contabilidad;

use App\Enums\TipoCuenta;
use App\Models\Periodo;

/**
 * Hoja de trabajo de 10 columnas (5 pares Debe/Haber):
 *   1. Balanza de comprobacion (saldos antes de ajustes)
 *   2. Ajustes (movimientos de las partidas de ajuste)
 *   3. Balanza ajustada (1 + 2)
 *   4. Estado de resultados (cuentas de costo, gasto, ingreso)
 *   5. Balance general (cuentas de activo, pasivo, patrimonio)
 * La utilidad = Haber - Debe de las columnas del estado de resultados. Se agrega
 * en ER-Debe y BG-Haber (o al reves si es perdida) y asi las columnas cuadran.
 */
class HojaTrabajo
{
    /** Tipos de cuenta que van al estado de resultados; el resto va al balance */
    public const DE_RESULTADOS = [TipoCuenta::Costo, TipoCuenta::Gasto, TipoCuenta::Ingreso, TipoCuenta::Liquidadora];

    public const COLUMNAS = ['bal_d', 'bal_h', 'aju_d', 'aju_h', 'adj_d', 'adj_h', 'er_d', 'er_h', 'bg_d', 'bg_h'];

    public static function generar(Periodo $periodo): array
    {
        $cuentas = Saldos::cuentas();
        $antes = Saldos::porCuenta($periodo, Saldos::SIN_AJUSTES);
        $ajustes = Saldos::porCuenta($periodo, Saldos::AJUSTES);

        $totales = array_fill_keys(self::COLUMNAS, 0);
        $filas = [];

        foreach (array_unique(array_merge(array_keys($antes), array_keys($ajustes))) as $id) {
            $cuenta = $cuentas[$id];
            $netoAntes = ($antes[$id]['debe'] ?? 0) - ($antes[$id]['haber'] ?? 0);
            $ajusteDebe = $ajustes[$id]['debe'] ?? 0;
            $ajusteHaber = $ajustes[$id]['haber'] ?? 0;
            $netoAjustado = $netoAntes + $ajusteDebe - $ajusteHaber;

            $resultados = in_array($cuenta->tipo, self::DE_RESULTADOS, true);

            $fila = [
                'cuenta' => $cuenta,
                'bal_d' => max($netoAntes, 0), 'bal_h' => max(-$netoAntes, 0),
                'aju_d' => $ajusteDebe, 'aju_h' => $ajusteHaber,
                'adj_d' => max($netoAjustado, 0), 'adj_h' => max(-$netoAjustado, 0),
                'er_d' => $resultados ? max($netoAjustado, 0) : 0,
                'er_h' => $resultados ? max(-$netoAjustado, 0) : 0,
                'bg_d' => $resultados ? 0 : max($netoAjustado, 0),
                'bg_h' => $resultados ? 0 : max(-$netoAjustado, 0),
            ];
            $filas[] = $fila;

            foreach (self::COLUMNAS as $c) {
                $totales[$c] += $fila[$c];
            }
        }

        usort($filas, fn ($a, $b) => strcmp($a['cuenta']->codigo, $b['cuenta']->codigo));

        $utilidad = $totales['er_h'] - $totales['er_d'];   // negativa = perdida

        // Totales finales: con la utilidad (o perdida) ya sumada donde corresponde
        $finales = $totales;
        if ($utilidad >= 0) {
            $finales['er_d'] += $utilidad;
            $finales['bg_h'] += $utilidad;
        } else {
            $finales['er_h'] += -$utilidad;
            $finales['bg_d'] += -$utilidad;
        }

        return [
            'filas' => $filas,
            'totales' => $totales,
            'utilidad' => $utilidad,
            'finales' => $finales,
            'comprobaciones' => [
                'Balanza de comprobación' => $totales['bal_d'] === $totales['bal_h'],
                'Ajustes' => $totales['aju_d'] === $totales['aju_h'],
                'Balanza ajustada' => $totales['adj_d'] === $totales['adj_h'],
                'Estado de resultados' => $finales['er_d'] === $finales['er_h'],
                'Balance general' => $finales['bg_d'] === $finales['bg_h'],
            ],
        ];
    }
}