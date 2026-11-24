<?php

namespace App\Services\Contabilidad;

use App\Enums\TipoPartida;
use App\Models\Cuenta;
use App\Models\Periodo;
use App\Support\Dinero;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Libro mayor de UNA cuenta: todos sus movimientos del periodo en orden de fecha,
 * con el saldo acumulado. Si la cuenta es de grupo (p. ej. 1101 Efectivo), junta
 * los movimientos de todas sus subcuentas (110101, 110102, 110103...).
 * Incluye todas las partidas (apertura, diario, ajuste y cierre).
 */
class LibroMayor
{
    public static function generar(Cuenta $cuenta, Periodo $periodo): array
    {
        $filas = DB::table('detalle_partidas')
            ->join('partidas', 'partidas.id', '=', 'detalle_partidas.partida_id')
            ->join('cuentas', 'cuentas.id', '=', 'detalle_partidas.cuenta_id')
            ->where('partidas.periodo_id', $periodo->id)
            ->where('cuentas.codigo', 'like', $cuenta->codigo.'%')
            ->orderBy('partidas.fecha')->orderBy('partidas.numero')->orderBy('detalle_partidas.id')
            ->select('partidas.id as partida_id', 'partidas.numero', 'partidas.fecha', 'partidas.tipo',
                'partidas.concepto as concepto_partida', 'detalle_partidas.concepto',
                'detalle_partidas.debe', 'detalle_partidas.haber', 'cuentas.codigo', 'cuentas.nombre')
            ->get();

        $saldo = 0;
        $totalDebe = 0;
        $totalHaber = 0;
        $movimientos = [];

        foreach ($filas as $f) {
            $debe = Dinero::centavos($f->debe);
            $haber = Dinero::centavos($f->haber);
            $totalDebe += $debe;
            $totalHaber += $haber;
            $saldo += Saldos::saldo($cuenta->naturaleza, $debe, $haber);

            $movimientos[] = [
                'partida_id' => $f->partida_id,
                'numero' => $f->numero,
                'fecha' => Carbon::parse($f->fecha),
                'tipo' => TipoPartida::from($f->tipo),
                'concepto' => $f->concepto ?: $f->concepto_partida,
                'cuenta' => $f->codigo === $cuenta->codigo ? null : "{$f->codigo} - {$f->nombre}",
                'debe' => $debe,
                'haber' => $haber,
                'saldo' => $saldo,
            ];
        }

        return [
            'movimientos' => $movimientos,
            'total_debe' => $totalDebe,
            'total_haber' => $totalHaber,
            'saldo' => $saldo,   // positivo = saldo normal de la cuenta; negativo = saldo contrario
        ];
    }
}