<?php

namespace App\Services\Contabilidad;

use App\Models\Periodo;

/**
 * Balanza de comprobacion: por cada cuenta, el total del debe y del haber (movimientos)
 * y su saldo, puesto en la columna deudora o acreedora segun hacia donde quedo.
 * Si la partida doble se respeto, las sumas de las dos columnas de saldos son iguales.
 *
 * $ajustada = false -> antes de ajustes (apertura + diario)
 * $ajustada = true  -> despues de ajustes (apertura + diario + ajuste)
 * $nivel = null -> cuentas de detalle ; 3 -> cuentas de mayor (1101, 2101...)
 */
class BalanzaComprobacion
{
    public static function generar(Periodo $periodo, bool $ajustada = false, ?int $nivel = null): array
    {
        $cuentas = Saldos::cuentas();
        $movimientos = Saldos::porCuenta($periodo, $ajustada ? Saldos::AJUSTADOS : Saldos::SIN_AJUSTES);

        if ($nivel) {
            $movimientos = Saldos::agrupar($movimientos, $nivel, $cuentas);
        }

        $filas = [];
        $totales = ['debe' => 0, 'haber' => 0, 'deudor' => 0, 'acreedor' => 0];

        foreach ($movimientos as $id => $m) {
            $neto = $m['debe'] - $m['haber'];
            $fila = [
                'cuenta' => $cuentas[$id],
                'debe' => $m['debe'],
                'haber' => $m['haber'],
                'deudor' => max($neto, 0),
                'acreedor' => max(-$neto, 0),
            ];
            $filas[] = $fila;

            foreach ($totales as $columna => $valor) {
                $totales[$columna] = $valor + $fila[$columna];
            }
        }

        usort($filas, fn ($a, $b) => strcmp($a['cuenta']->codigo, $b['cuenta']->codigo));

        return [
            'filas' => $filas,
            'totales' => $totales,
            'cuadra' => $totales['debe'] === $totales['haber'] && $totales['deudor'] === $totales['acreedor'],
        ];
    }
}