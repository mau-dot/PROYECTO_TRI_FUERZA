<?php

namespace App\Services\Contabilidad;

use App\Enums\Naturaleza;
use App\Enums\TipoPartida;
use App\Models\Cuenta;
use App\Models\Periodo;
use App\Support\Dinero;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Base de todos los reportes: cuanto se cargo (debe) y abono (haber) a cada cuenta
 * en un periodo, en CENTAVOS. Los demas servicios solo ordenan y suman estos numeros.
 *
 * Que partidas cuentan (las de cierre nunca entran a la hoja de trabajo ni a los estados):
 *   SIN_AJUSTES -> apertura + diario    (balanza "antes de ajustes")
 *   AJUSTES     -> solo ajuste           (columnas de ajustes)
 *   AJUSTADOS   -> apertura + diario + ajuste (balanza ajustada y estados financieros)
 *   TODAS       -> también las de cierre (solo el libro mayor)
 */
class Saldos
{
    public const SIN_AJUSTES = [TipoPartida::Apertura, TipoPartida::Diario];

    public const AJUSTES = [TipoPartida::Ajuste];

    public const AJUSTADOS = [TipoPartida::Apertura, TipoPartida::Diario, TipoPartida::Ajuste];

    public const TODAS = [TipoPartida::Apertura, TipoPartida::Diario, TipoPartida::Ajuste, TipoPartida::Cierre];

    /**
     * Suma de debe y haber por cuenta.
     *
     * @param  TipoPartida[]  $tipos
     * @return array<int, array{debe: int, haber: int}>  clave = cuenta_id
     */
    public static function porCuenta(Periodo $periodo, array $tipos): array
    {
        $filas = DB::table('detalle_partidas')
            ->join('partidas', 'partidas.id', '=', 'detalle_partidas.partida_id')
            ->where('partidas.periodo_id', $periodo->id)
            ->whereIn('partidas.tipo', array_map(fn (TipoPartida $t) => $t->value, $tipos))
            ->groupBy('detalle_partidas.cuenta_id')
            ->select('detalle_partidas.cuenta_id',
                DB::raw('SUM(detalle_partidas.debe) as debe'),
                DB::raw('SUM(detalle_partidas.haber) as haber'))
            ->get();

        $resultado = [];
        foreach ($filas as $f) {
            $resultado[$f->cuenta_id] = ['debe' => Dinero::centavos($f->debe), 'haber' => Dinero::centavos($f->haber)];
        }

        return $resultado;
    }

    /** Todas las cuentas, con clave = id (para subir por el arbol sin más consultas) */
    public static function cuentas(): Collection
    {
        return Cuenta::query()->orderBy('codigo')->get()->keyBy('id');
    }

    /** La cuenta "abuela" de nivel $nivel (o la misma cuenta si ya está en ese nivel o más arriba) */
    public static function ancestro(Cuenta $cuenta, int $nivel, Collection $cuentas): Cuenta
    {
        while ($cuenta->nivel > $nivel && $cuenta->padre_id && isset($cuentas[$cuenta->padre_id])) {
            $cuenta = $cuentas[$cuenta->padre_id];
        }

        return $cuenta;
    }

    /**
     * Junta los movimientos en las cuentas de nivel $nivel (p. ej. 3 = cuentas de mayor "1101").
     *
     * @return array<int, array{debe: int, haber: int}>
     */
    public static function agrupar(array $movimientos, int $nivel, Collection $cuentas): array
    {
        $resultado = [];
        foreach ($movimientos as $id => $m) {
            $destino = self::ancestro($cuentas[$id], $nivel, $cuentas)->id;
            $resultado[$destino] ??= ['debe' => 0, 'haber' => 0];
            $resultado[$destino]['debe'] += $m['debe'];
            $resultado[$destino]['haber'] += $m['haber'];
        }

        return $resultado;
    }

    /** Saldo segun la naturaleza: deudora = debe - haber; acreedora = haber - debe */
    public static function saldo(Naturaleza $naturaleza, int $debe, int $haber): int
    {
        return $naturaleza === Naturaleza::Deudora ? $debe - $haber : $haber - $debe;
    }
}

