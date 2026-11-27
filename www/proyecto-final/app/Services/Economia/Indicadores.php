<?php

namespace App\Services\Economia;

use App\Models\Alternativa;
use InvalidArgumentException;

/**
 * Fase 7: indicadores de evaluación económica.
 * Las tasas se reciben en decimal: 15 % = 0.15.
 */
class Indicadores
{
    /**
     * Valor presente neto de una serie de flujos.
     * El primer elemento corresponde al año cero.
     */
    public static function vpn(array $flujos, float $i): float
    {
        if ($i <= -1) {
            throw new InvalidArgumentException(
                'La tasa debe ser mayor que -100 %.'
            );
        }

        $vpn = 0.0;

        foreach (array_values($flujos) as $n => $flujo) {
            $vpn += $flujo / (1 + $i) ** $n;
        }

        return $vpn;
    }

    /**
     * Tasa interna de rendimiento mediante bisección.
     * Para flujos convencionales:
     * inversión inicial negativa y flujos posteriores
     * no negativos.
     *
     * Devuelve null cuando no existe una TIR válida
     * para este tipo de flujo.
     */
    public static function tir(array $flujos): ?float
    {
        $flujos = array_values($flujos);

        if (
            count($flujos) < 2 ||
            $flujos[0] >= 0
        ) {
            return null;
        }

        $posteriores = array_slice($flujos, 1);

        if (
            min($posteriores) < 0 ||
            max($posteriores) <= 0
        ) {
            return null;
        }

        $inferior = -0.99;
        $superior = 1.0;

        // Ampliar el intervalo si es necesario.
        while (self::vpn($flujos, $superior) > 0) {

            $superior = $superior * 2 + 1;

            if ($superior > 1000000) {
                return null;
            }
        }

        if (self::vpn($flujos, $inferior) < 0) {
            return null;
        }

        // Método de bisección.
        for ($k = 0; $k < 200; $k++) {

            $media = ($inferior + $superior) / 2;

            $valor = self::vpn($flujos, $media);

            if (
                abs($valor) < 0.0000001 ||
                abs($superior - $inferior) < 0.0000000001
            ) {
                return $media;
            }

            if ($valor > 0) {
                $inferior = $media;
            } else {
                $superior = $media;
            }
        }

        return ($inferior + $superior) / 2;
    }

    /**
     * Valor anual uniforme equivalente neto.
     */
    public static function caue(
        float $vpn,
        float $i,
        int $n
    ): float
    {
        return $vpn * Factores::ap($i, $n);
    }

    /**
     * Costo anual uniforme equivalente.
     * Se expresa como costo positivo.
     */
    public static function costoAnual(
        float $inversion,
        float $salvamento,
        float $costoOperacion,
        float $i,
        int $n
    ): float
    {
        return
            $inversion * Factores::ap($i, $n)
            - $salvamento * Factores::af($i, $n)
            + $costoOperacion;
    }

    /**
     * Relación beneficio/costo.
     */
    public static function bc(
        float $vpBeneficios,
        float $vpCostos
    ): ?float
    {
        if ($vpCostos <= 0) {
            return null;
        }

        return $vpBeneficios / $vpCostos;
    }

    /**
     * Evaluación de una alternativa antes de impuestos.
     */
    public static function evaluar(
        Alternativa $a,
        float $i
    ): array
    {
        $p = (float) $a->inversion;
        $vs = (float) $a->valor_salvamento;
        $n = (int) $a->vida_util;
        $beneficio = (float) $a->beneficio_anual;

        // Se consulta el costo desde Contabilidad.
        $costo = $a->costoOperacion();

        $filas = FlujosCaja::generar($a);

        $flujos = array_column($filas, 'neto');

        // Factores de interés.
        $pa = Factores::pa($i, $n);
        $pf = Factores::pf($i, $n);
        $ap = Factores::ap($i, $n);
        $af = Factores::af($i, $n);

        // Valores presentes.
        $vpBeneficios = $beneficio * $pa;

        $vpCostos = $p + ($costo * $pa) - ($vs * $pf);

        // Indicadores.
        $vpn = self::vpn($flujos, $i);
        $tir = self::tir($flujos);
        $caue = self::caue($vpn, $i, $n);
        $bc = self::bc($vpBeneficios, $vpCostos);

        return [
            'vpn' => $vpn,
            'tir' => $tir,
            'caue' => $caue,
            'bc' => $bc,

            'caue_costos' => self::costoAnual(
                $p,
                $vs,
                $costo,
                $i,
                $n
            ),

            'inversion' => $p,
            'vida_util' => $n,
            'salvamento' => $vs,
            'beneficio_anual' => $beneficio,
            'costo_operacion' => $costo,
            'flujo_anual' => $beneficio - $costo,

            'vp_beneficios' => $vpBeneficios,
            'vp_costos' => $vpCostos,

            'factores' => [
                'pa' => $pa,
                'pf' => $pf,
                'ap' => $ap,
                'af' => $af,
            ],

            'flujos' => $flujos,

            'aceptable' => $vpn >= 0,

            'origen_costo' => $a->origenCosto(),

            'provisional' =>
                $a->provisional
                || ($a->decision?->provisional ?? false)
                || ($a->costeo?->provisional ?? false),
        ];
    }
}
