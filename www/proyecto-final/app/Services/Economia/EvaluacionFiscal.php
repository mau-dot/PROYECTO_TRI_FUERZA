<?php

namespace App\Services\Economia;

use App\Models\Alternativa;
use InvalidArgumentException;

/**
 * Fase 7: evaluación económica después de impuestos.
 *
 * Las tasas se reciben en decimal:
 * TMAR 15 % = 0.15
 * ISR 25 % = 0.25
 */
class EvaluacionFiscal
{
    /**
     * Genera los flujos después de impuestos.
     *
     * Metodos de depreciacion:
     * lr  = Linea recta
     * sda = Suma de digitos de los años
     * sdd = Saldo doble decreciente
     */
    public static function generar(
        Alternativa $a,
        float $t,
        string $metodo = 'lr',
        bool $aplicarEscudoFiscal = true
    ): array
    {
        if ($t < 0 || $t > 1) {
            throw new InvalidArgumentException(
                'La tasa de ISR debe estar entre 0 % y 100 %.'
            );
        }

        if (! array_key_exists(
            $metodo,
            Depreciacion::METODOS
        )) {
            throw new InvalidArgumentException(
                'Metodo de depreciacion no valido.'
            );
        }

        $p = (float) $a->inversion;
        $vs = (float) $a->valor_salvamento;
        $n = (int) $a->vida_util;

        $beneficio = (float) $a->beneficio_anual;

        // Costo obtenido desde Contabilidad.
        $costo = $a->costoOperacion();

        // Reutilizamos el servicio de la fase 6.
        $depreciaciones = Depreciacion::todas(
            $p,
            $vs,
            $n
        )[$metodo];

        // Año cero: inversion inicial.
        $filas = [
            [
                'anio' => 0,
                'inversion' => -$p,
                'beneficio' => 0.0,
                'costo' => 0.0,
                'depreciacion' => 0.0,
                'utilidad_gravable' => 0.0,
                'isr' => 0.0,
                'utilidad_neta' => 0.0,
                'valor_libros' => $p,
                'salvamento' => 0.0,
                'impuesto_salvamento' => 0.0,
                'salvamento_neto' => 0.0,
                'fedi' => -$p,
            ],
        ];

        // Calcular cada año de vida util.
        for ($anio = 1; $anio <= $n; $anio++) {

            $d = (float) $depreciaciones[
                $anio - 1
            ]['depreciacion'];

            $libros = (float) $depreciaciones[
                $anio - 1
            ]['libros'];

            // Utilidad sujeta a impuestos.
            $utilidadGravable =
                $beneficio - $costo - $d;

            // Permite simular el aprovechamiento
            // de perdidas fiscales.
            $baseFiscal = $aplicarEscudoFiscal
                ? $utilidadGravable
                : max(0, $utilidadGravable);

            $impuesto = $baseFiscal * $t;

            // Utilidad despues de impuestos.
            $utilidadNeta =
                $utilidadGravable - $impuesto;

            // Salvamento al finalizar la vida util.
            $salvamento = $anio === $n
                ? $vs
                : 0.0;

            $impuestoSalvamento = 0.0;

            if ($anio === $n) {

                $gananciaVenta =
                    $salvamento - $libros;

                if (! $aplicarEscudoFiscal) {
                    $gananciaVenta = max(
                        0,
                        $gananciaVenta
                    );
                }

                $impuestoSalvamento =
                    $gananciaVenta * $t;
            }

            $salvamentoNeto =
                $salvamento - $impuestoSalvamento;

            // La depreciacion no es salida de efectivo.
            $fedi =
                $utilidadNeta
                + $d
                + $salvamentoNeto;

            $filas[] = [
                'anio' => $anio,
                'inversion' => 0.0,
                'beneficio' => $beneficio,
                'costo' => -$costo,
                'depreciacion' => $d,
                'utilidad_gravable' => $utilidadGravable,
                'isr' => $impuesto,
                'utilidad_neta' => $utilidadNeta,
                'valor_libros' => $libros,
                'salvamento' => $salvamento,
                'impuesto_salvamento' => $impuestoSalvamento,
                'salvamento_neto' => $salvamentoNeto,
                'fedi' => $fedi,
            ];
        }

        return $filas;
    }

    /**
     * Calcula los indicadores despues de impuestos.
     */
    public static function evaluar(
        Alternativa $a,
        float $i,
        float $t,
        string $metodo = 'lr',
        bool $aplicarEscudoFiscal = true
    ): array
    {
        $filas = self::generar(
            $a,
            $t,
            $metodo,
            $aplicarEscudoFiscal
        );

        $flujos = array_column($filas, 'fedi');

        $n = (int) $a->vida_util;

        // Reutilizamos los indicadores economicos.
        $vpn = Indicadores::vpn($flujos, $i);

        $tir = Indicadores::tir($flujos);

        $caue = Indicadores::caue(
            $vpn,
            $i,
            $n
        );

        // Valores presentes de beneficios y costos.
        $vpBeneficios = 0.0;
        $vpCostos = (float) $a->inversion;

        foreach ($filas as $fila) {

            $anio = $fila['anio'];

            if ($anio === 0) {
                continue;
            }

            $factor = 1 / (1 + $i) ** $anio;

            $vpBeneficios +=
                $fila['beneficio'] * $factor;

            $vpCostos += (
                abs($fila['costo'])
                + $fila['isr']
                - $fila['salvamento_neto']
            ) * $factor;
        }

        $bc = Indicadores::bc(
            $vpBeneficios,
            $vpCostos
        );

        return [
            'metodo' => $metodo,

            'nombre_metodo' =>
                Depreciacion::METODOS[$metodo],

            'tmar' => $i,
            'tasa_isr' => $t,

            'vpn' => $vpn,
            'tir' => $tir,
            'caue' => $caue,
            'bc' => $bc,

            'vp_beneficios' => $vpBeneficios,
            'vp_costos' => $vpCostos,

            'filas' => $filas,
            'flujos' => $flujos,

            'total_depreciacion' => array_sum(
                array_column($filas, 'depreciacion')
            ),

            'total_isr' => array_sum(
                array_column($filas, 'isr')
            ) + array_sum(
                array_column($filas, 'impuesto_salvamento')
            ),

            'aceptable' => $vpn >= 0,

            'escudo_fiscal' => $aplicarEscudoFiscal,

            'provisional' =>
                $a->provisional
                || ($a->decision?->provisional ?? false)
                || ($a->costeo?->provisional ?? false),
        ];
    }

    /**
     * Evalua la alternativa mediante los tres
     * metodos de depreciacion.
     */
    public static function todos(
        Alternativa $a,
        float $i,
        float $t,
        bool $aplicarEscudoFiscal = true
    ): array
    {
        $resultados = [];

        foreach (
            Depreciacion::METODOS as $metodo => $nombre
        ) {

            $resultados[$metodo] = self::evaluar(
                $a,
                $i,
                $t,
                $metodo,
                $aplicarEscudoFiscal
            );
        }

        return $resultados;
    }
}
