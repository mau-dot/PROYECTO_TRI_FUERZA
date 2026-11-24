<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Cuenta;
use App\Models\Empresa;
use App\Models\Periodo;
use App\Services\Contabilidad\BalanzaComprobacion;
use App\Services\Contabilidad\EstadosFinancieros;
use App\Services\Contabilidad\HojaTrabajo;
use App\Services\Contabilidad\LibroMayor;
use App\Services\Contabilidad\Saldos;
use Illuminate\Http\Request;

/**
 * Reportes contables del periodo activo. Aqui no se calcula nada:
 * los calculos están en app/Services/Contabilidad.
 */
class ReporteController extends Controller
{
    /** Libro mayor: sin ?cuenta= muestra la lista de cuentas con movimiento; con ?cuenta=ID, su mayor y su cuenta T */
    public function mayor(Request $request)
    {
        if (! $periodo = Periodo::activo()) {
            return $this->sinPeriodo();
        }

        $empresa = Empresa::actual();
        $cuentas = Saldos::cuentas();
        $cuenta = $request->filled('cuenta') ? Cuenta::findOrFail($request->integer('cuenta')) : null;

        if ($cuenta) {
            $mayor = LibroMayor::generar($cuenta, $periodo);

            return view('contabilidad.reportes.mayor', compact('periodo', 'empresa', 'cuentas', 'cuenta', 'mayor') + ['resumen' => []]);
        }

        // Resumen: cada cuenta de detalle con movimiento y su saldo
        $resumen = [];
        foreach (Saldos::porCuenta($periodo, Saldos::TODAS) as $id => $m) {
            $c = $cuentas[$id];
            $resumen[] = ['cuenta' => $c, 'debe' => $m['debe'], 'haber' => $m['haber'], 'saldo' => Saldos::saldo($c->naturaleza, $m['debe'], $m['haber'])];
        }
        usort($resumen, fn ($a, $b) => strcmp($a['cuenta']->codigo, $b['cuenta']->codigo));

        return view('contabilidad.reportes.mayor', compact('periodo', 'empresa', 'cuentas', 'cuenta', 'resumen') + ['mayor' => null]);
    }

    /** ?ajustada=1 -> despues de ajustes ; ?nivel=mayor -> agrupa en cuentas de mayor (nivel 3) */
    public function balanza(Request $request)
    {
        if (! $periodo = Periodo::activo()) {
            return $this->sinPeriodo();
        }

        $ajustada = $request->boolean('ajustada');
        $nivel = $request->query('nivel') === 'mayor' ? 'mayor' : 'detalle';
        $balanza = BalanzaComprobacion::generar($periodo, $ajustada, $nivel === 'mayor' ? 3 : null);
        $empresa = Empresa::actual();

        return view('contabilidad.reportes.balanza', compact('periodo', 'empresa', 'balanza', 'ajustada', 'nivel'));
    }

    public function hojaTrabajo()
    {
        if (! $periodo = Periodo::activo()) {
            return $this->sinPeriodo();
        }

        $hoja = HojaTrabajo::generar($periodo);
        $empresa = Empresa::actual();

        return view('contabilidad.reportes.hoja-trabajo', compact('periodo', 'empresa', 'hoja'));
    }

    /** ?estado=resultados (por defecto) o ?estado=balance */
    public function estados(Request $request)
    {
        if (! $periodo = Periodo::activo()) {
            return $this->sinPeriodo();
        }

        $estado = $request->query('estado') === 'balance' ? 'balance' : 'resultados';
        $resultados = EstadosFinancieros::resultados($periodo);
        $balance = EstadosFinancieros::balance($periodo, $resultados['utilidad_neta']);
        $hoja = HojaTrabajo::generar($periodo);   // para comprobar que la utilidad coincide
        $empresa = Empresa::actual();

        return view('contabilidad.reportes.estados', compact('periodo', 'empresa', 'estado', 'resultados', 'balance', 'hoja'));
    }

    private function sinPeriodo()
    {
        return redirect()->route('contabilidad.periodos.index')
            ->with('warning', 'Primero crea o activa un periodo contable.');
    }
}