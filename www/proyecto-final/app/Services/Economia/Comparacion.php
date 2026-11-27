<?php

namespace App\Services\Economia;

use App\Models\Alternativa;
use App\Models\Decision;

/**
 * Fase 7: comparacion de alternativas mutuamente excluyentes.
 *
 * Se utiliza el valor anual equivalente para comparar
 * alternativas con diferentes vidas utiles.
 *
 * Supuesto: las alternativas pueden repetirse
 * en condiciones economicas equivalentes.
 */
class Comparacion
{
    /**
     * Calcula el valor anual equivalente de una alternativa.
     */
    private static function valorAnual(
        array $flujos,
        float $i,
        int $n
    ): float
    {
        $vpn = Indicadores::vpn($flujos, $i);

        if (abs($i) < 0.000000001) {
            return $vpn / $n;
        }

        return Indicadores::caue($vpn, $i, $n);
    }

    /**
     * Calcula la diferencia entre los valores anuales
     * equivalentes del retador y el defensor.
     */
    private static function diferencia(
        array $defensor,
        array $retador,
        float $i
    ): float
    {
        $vaDefensor = self::valorAnual(
            $defensor['flujos'],
            $i,
            $defensor['vida_util']
        );

        $vaRetador = self::valorAnual(
            $retador['flujos'],
            $i,
            $retador['vida_util']
        );

        return $vaRetador - $vaDefensor;
    }

    /**
     * Busca tasas de cruce positivas mediante biseccion.
     *
     * Una tasa de cruce hace iguales los valores anuales
     * equivalentes de ambas alternativas.
     *
     * Si existen varias tasas detectadas, no se utiliza
     * una sola TIR incremental como criterio de decision.
     */
    public static function tasasCruce(
        array $defensor,
        array $retador
    ): array
    {
        $raices = [];

        $anterior = 0.0;

        $fAnterior = self::diferencia(
            $defensor,
            $retador,
            $anterior
        );

        // Buscar entre 0 % y 1000 %.
        $limite = 10.0;
        $paso = 0.005;

        // Evitar atribuir una TIR a flujos identicos.
        if (
            $defensor['vida_util'] === $retador['vida_util']
            && $defensor['flujos'] === $retador['flujos']
        ) {
            return [];
        }

        for ($j = 1; $j <= 2000; $j++) {

            $actual = min($j * $paso, $limite);

            $fActual = self::diferencia(
                $defensor,
                $retador,
                $actual
            );

            $raiz = null;

            if (abs($fAnterior) < 0.0000001) {

                $raiz = $anterior;

            } elseif (abs($fActual) < 0.0000001) {

                $raiz = $actual;

            } elseif (
                ($fAnterior > 0 && $fActual < 0)
                || ($fAnterior < 0 && $fActual > 0)
            ) {

                $izquierda = $anterior;
                $derecha = $actual;
                $fIzquierda = $fAnterior;

                for ($k = 0; $k < 100; $k++) {

                    $media =
                        ($izquierda + $derecha) / 2;

                    $fMedia = self::diferencia(
                        $defensor,
                        $retador,
                        $media
                    );

                    if (
                        abs($fMedia) < 0.0000001
                        || abs($derecha - $izquierda)
                            < 0.0000000001
                    ) {
                        $izquierda = $media;
                        $derecha = $media;
                        break;
                    }

                    if (
                        ($fIzquierda > 0 && $fMedia > 0)
                        || ($fIzquierda < 0 && $fMedia < 0)
                    ) {

                        $izquierda = $media;
                        $fIzquierda = $fMedia;

                    } else {

                        $derecha = $media;
                    }
                }

                $raiz =
                    ($izquierda + $derecha) / 2;
            }

            if ($raiz !== null) {

                $ultima = empty($raices)
                    ? null
                    : $raices[count($raices) - 1];

                if (
                    $ultima === null
                    || abs($raiz - $ultima) > 0.000001
                ) {
                    $raices[] = $raiz;
                }
            }

            $anterior = $actual;
            $fAnterior = $fActual;
        }

        return $raices;
    }

    /**
     * Devuelve una TIR incremental cuando se detecta
     * una unica tasa de cruce positiva o igual a cero.
     */
    public static function tirIncremental(
        array $defensor,
        array $retador
    ): ?float
    {
        $tasas = self::tasasCruce(
            $defensor,
            $retador
        );

        return count($tasas) === 1
            ? $tasas[0]
            : null;
    }

    /**
     * Prepara los indicadores de una alternativa.
     */
    private static function preparar(
        Alternativa $a,
        float $i,
        float $t,
        bool $despuesImpuestos,
        string $metodo,
        bool $escudoFiscal
    ): array
    {
        $antes = Indicadores::evaluar($a, $i);

        if ($despuesImpuestos) {

            $resultado = EvaluacionFiscal::evaluar(
                $a,
                $i,
                $t,
                $metodo,
                $escudoFiscal
            );

        } else {

            $resultado = $antes;
        }

        return [
            'id' => $a->id,

            'nombre' => $a->nombre,

            'inversion' => (float) $a->inversion,

            'vida_util' => (int) $a->vida_util,

            'vpn' => $resultado['vpn'],

            'tir' => $resultado['tir'],

            'caue' => $resultado['caue'],

            'bc' => $resultado['bc'],

            'flujos' => $resultado['flujos'],

            'aceptable' =>
                $resultado['vpn'] >= 0,

            'origen_costo' => $a->origenCosto(),

            'provisional' =>
                $resultado['provisional'],
        ];
    }

    /**
     * Ejecuta la comparacion completa.
     */
    public static function evaluar(
        Decision $decision,
        bool $despuesImpuestos = false,
        string $metodo = 'lr',
        bool $escudoFiscal = true
    ): array
    {
        $i = $decision->i();

        $t = $decision->t();

        // Ordenar de menor a mayor inversion.
        $alternativas = $decision->alternativas()
            ->reorder('inversion', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $resultados = [];

        foreach ($alternativas as $a) {

            $resultados[] = self::preparar(
                $a,
                $i,
                $t,
                $despuesImpuestos,
                $metodo,
                $escudoFiscal
            );
        }

        // Comparar inicialmente contra no invertir.
        $defensor = null;

        $pasos = [];

        foreach ($resultados as $retador) {

            // Buscar la primera alternativa aceptable.
            if ($defensor === null) {

                if ($retador['caue'] > 0.000001) {

                    $defensor = $retador;

                    $estado = 'aceptar';

                    $mensaje =
                        'La alternativa genera valor '
                        .'y supera a no invertir.';

                } else {

                    $estado = 'descartar';

                    $mensaje =
                        'La alternativa no genera un '
                        .'valor anual neto positivo.';
                }

                $pasos[] = [
                    'defensor' => 'No invertir',

                    'retador' => $retador['nombre'],

                    'inversion_incremental' =>
                        $retador['inversion'],

                    'tir_incremental' =>
                        $retador['tir'],

                    'tasas_cruce' => [],

                    'caue_incremental' =>
                        $retador['caue'],

                    'decision' => $estado,

                    'mensaje' => $mensaje,
                ];

                continue;
            }

            // Comparacion incremental.
            $deltaInversion =
                $retador['inversion']
                - $defensor['inversion'];

            $deltaCaue =
                $retador['caue']
                - $defensor['caue'];

            $tasas = self::tasasCruce(
                $defensor,
                $retador
            );

            $tirIncremental = count($tasas) === 1
                ? $tasas[0]
                : null;

            // El criterio decisivo es el valor anual
            // equivalente incremental a la TMAR.
            if ($deltaCaue > 0.000001) {

                $estado = 'aceptar';

                $mensaje =
                    'La alternativa retadora genera '
                    .'mayor valor anual equivalente.';

            } elseif ($deltaCaue < -0.000001) {

                $estado = 'descartar';

                $mensaje =
                    'La alternativa retadora no '
                    .'justifica el cambio economico.';

            } else {

                $estado = 'indiferente';

                $mensaje =
                    'Las alternativas tienen valores '
                    .'anuales equivalentes.';
            }

            $pasos[] = [
                'defensor' => $defensor['nombre'],

                'retador' => $retador['nombre'],

                'inversion_incremental' =>
                    $deltaInversion,

                'tir_incremental' =>
                    $tirIncremental,

                'tasas_cruce' => $tasas,

                'caue_incremental' => $deltaCaue,

                'decision' => $estado,

                'mensaje' => $mensaje,
            ];

            if ($estado === 'aceptar') {

                $defensor = $retador;
            }
        }

        // Clasificacion por valor anual equivalente.
        $ranking = $resultados;

        usort(
            $ranking,
            fn ($a, $b) => $b['caue'] <=> $a['caue']
        );

        $provisional = (bool) $decision->provisional;

        foreach ($resultados as $resultado) {

            if ($resultado['provisional']) {
                $provisional = true;
                break;
            }
        }

        return [
            'tmar' => $i,

            'tasa_isr' => $t,

            'despues_impuestos' => $despuesImpuestos,

            'metodo' => $metodo,

            'alternativas' => $resultados,

            'ranking' => $ranking,

            'pasos' => $pasos,

            'recomendada' => $defensor,

            'mensaje' => $defensor
                ? 'Se recomienda '.$defensor['nombre']
                : 'No se recomienda realizar ninguna inversion.',

            'criterio' =>
                'Mayor valor anual equivalente neto a la TMAR.',

            'supuesto' =>
                'Alternativas repetibles en condiciones equivalentes.',

            'provisional' => $provisional,
        ];
    }
}
