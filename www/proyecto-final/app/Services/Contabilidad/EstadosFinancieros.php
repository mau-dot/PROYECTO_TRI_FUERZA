<?php

namespace App\Services\Contabilidad;

use App\Enums\Naturaleza;
use App\Enums\Rubro;
use App\Enums\TipoCuenta;
use App\Models\Periodo;

/**
 * Estado de resultados y balance general, a partir de la balanza AJUSTADA
 * (apertura + diario + ajuste; las partidas de cierre no entran).
 *
 * Estado de resultados (secciones en config/estados_financieros.php):
 *   Ventas - Costo de venta                         = Utilidad bruta
 *   - Gastos de operación                           = Utilidad de operacion
 *   + Otros ingresos - Gastos no de operacion (± sin clasificar) = Utilidad antes de ISR
 *   - ISR                                           = Utilidad neta
 *
 * Balance general: Activo = Pasivo + Patrimonio (el patrimonio incluye la utilidad del ejercicio).
 */
class EstadosFinancieros
{
    public static function resultados(Periodo $periodo): array
    {
        $config = config('estados_financieros.resultados');
        $cuentas = Saldos::cuentas();
        $movimientos = Saldos::porCuenta($periodo, Saldos::AJUSTADOS);

        $secciones = [];
        foreach ($config as $clave => $s) {
            $secciones[$clave] = ['titulo' => $s['titulo'], 'lineas' => [], 'total' => 0];
        }
        $sinClasificar = ['titulo' => 'Sin clasificar', 'lineas' => [], 'total' => 0];

        foreach ($movimientos as $id => $m) {
            $cuenta = $cuentas[$id];
            if (! in_array($cuenta->tipo, HojaTrabajo::DE_RESULTADOS, true)) {
                continue;
            }

            $clave = self::seccionDe($cuenta->codigo, $config);

            if ($clave === null) {
                // se lee como ingreso (haber - debe): positivo aumenta la utilidad
                $monto = $m['haber'] - $m['debe'];
                $sinClasificar['lineas'][] = ['cuenta' => $cuenta, 'monto' => $monto];
                $sinClasificar['total'] += $monto;

                continue;
            }

            $monto = Saldos::saldo(Naturaleza::from($config[$clave]['naturaleza']), $m['debe'], $m['haber']);
            $secciones[$clave]['lineas'][] = ['cuenta' => $cuenta, 'monto' => $monto];
            $secciones[$clave]['total'] += $monto;
        }

        foreach (array_keys($secciones) as $clave) {
            usort($secciones[$clave]['lineas'], fn ($a, $b) => strcmp($a['cuenta']->codigo, $b['cuenta']->codigo));
        }

        $t = fn (string $clave) => $secciones[$clave]['total'] ?? 0;
        $utilidadBruta = $t('ventas') - $t('costo');
        $utilidadOperacion = $utilidadBruta - $t('gastos_operacion');
        $antesIsr = $utilidadOperacion + $t('otros_ingresos') - $t('otros_gastos') + $sinClasificar['total'];
        $utilidadNeta = $antesIsr - $t('isr');

        return [
            'secciones' => $secciones,
            'sin_clasificar' => $sinClasificar,
            'utilidad_bruta' => $utilidadBruta,
            'utilidad_operacion' => $utilidadOperacion,
            'utilidad_antes_isr' => $antesIsr,
            'utilidad_neta' => $utilidadNeta,
        ];
    }

    /** Clave de la sección cuyo prefijo mas largo calza con el codigo (null si ninguno) */
    public static function seccionDe(string $codigo, array $config): ?string
    {
        $elegida = null;
        $largo = 0;
        foreach ($config as $clave => $s) {
            foreach ($s['prefijos'] as $prefijo) {
                if (str_starts_with($codigo, (string) $prefijo) && strlen($prefijo) > $largo) {
                    $elegida = $clave;
                    $largo = strlen($prefijo);
                }
            }
        }

        return $elegida;
    }

    /**
     * @param  int  $utilidadNeta  la del estado de resultados (va al patrimonio)
     */
    public static function balance(Periodo $periodo, int $utilidadNeta): array
    {
        $cuentas = Saldos::cuentas();
        $nivel = (int) config('estados_financieros.nivel_balance', 3);
        $movimientos = Saldos::agrupar(Saldos::porCuenta($periodo, Saldos::AJUSTADOS), $nivel, $cuentas);

        $grupos = [];
        foreach ([TipoCuenta::Activo, TipoCuenta::Pasivo, TipoCuenta::Patrimonio] as $tipo) {
            $grupos[$tipo->value] = ['titulo' => mb_strtoupper($tipo->label()), 'rubros' => [], 'total' => 0];
        }

        foreach ($movimientos as $id => $m) {
            $cuenta = $cuentas[$id];
            if (! isset($grupos[$cuenta->tipo->value])) {
                continue;   // cuentas de resultado: ya estan en el estado de resultados
            }

            // saldo leido con la naturaleza NORMAL del grupo: las contra cuentas salen negativas
            $monto = Saldos::saldo($cuenta->tipo->naturalezaNormal(), $m['debe'], $m['haber']);
            if ($monto === 0) {
                continue;
            }

            $rubro = $cuenta->tipo === TipoCuenta::Patrimonio ? '' : ($cuenta->rubro?->value ?? 'sin_rubro');
            $grupos[$cuenta->tipo->value]['rubros'][$rubro] ??= [
                'titulo' => $rubro === '' ? '' : (Rubro::tryFrom($rubro)?->label() ?? 'Sin rubro'),
                'lineas' => [], 'total' => 0,
            ];
            $grupos[$cuenta->tipo->value]['rubros'][$rubro]['lineas'][] = ['texto' => $cuenta->etiqueta(), 'cuenta' => $cuenta, 'monto' => $monto];
            $grupos[$cuenta->tipo->value]['rubros'][$rubro]['total'] += $monto;
            $grupos[$cuenta->tipo->value]['total'] += $monto;
        }

        // La utilidad (o perdida) del ejercicio completa el patrimonio
        $grupos['patrimonio']['rubros'][''] ??= ['titulo' => '', 'lineas' => [], 'total' => 0];
        $grupos['patrimonio']['rubros']['']['lineas'][] = [
            'texto' => $utilidadNeta >= 0 ? 'Utilidad del ejercicio' : 'Pérdida del ejercicio',
            'cuenta' => null, 'monto' => $utilidadNeta,
        ];
        $grupos['patrimonio']['rubros']['']['total'] += $utilidadNeta;
        $grupos['patrimonio']['total'] += $utilidadNeta;

        // Orden: corriente antes que no corriente; las lineas por codigo (la utilidad al final)
        $orden = ['corriente' => 1, '' => 1, 'no_corriente' => 2, 'sin_rubro' => 3];
        foreach (array_keys($grupos) as $tipo) {
            uksort($grupos[$tipo]['rubros'], fn ($a, $b) => $orden[$a] <=> $orden[$b]);
            foreach (array_keys($grupos[$tipo]['rubros']) as $rubro) {
                usort($grupos[$tipo]['rubros'][$rubro]['lineas'],
                    fn ($a, $b) => [$a['cuenta'] === null, $a['cuenta']?->codigo] <=> [$b['cuenta'] === null, $b['cuenta']?->codigo]);
            }
        }

        $pasivoMasPatrimonio = $grupos['pasivo']['total'] + $grupos['patrimonio']['total'];

        return [
            'grupos' => $grupos,
            'total_activo' => $grupos['activo']['total'],
            'pasivo_mas_patrimonio' => $pasivoMasPatrimonio,
            'cuadra' => $grupos['activo']['total'] === $pasivoMasPatrimonio,
        ];
    }
}