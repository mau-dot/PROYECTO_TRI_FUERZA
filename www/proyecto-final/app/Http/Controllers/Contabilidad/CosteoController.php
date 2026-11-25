<?php

namespace App\Http\Controllers\Contabilidad;

use App\Enums\Comportamiento;
use App\Enums\ElementoCosto;
use App\Enums\SistemaCosteo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contabilidad\CosteoElementoRequest;
use App\Http\Requests\Contabilidad\CosteoRequest;
use App\Models\Costeo;
use App\Models\CosteoElemento;
use App\Models\Cuenta;
use App\Support\CosteosProvisionales;
use Illuminate\Support\Facades\DB;

/**
 * Sistema de costos : un costeo por alternativa de inversion, con sus elementos
 * Los calculos están en App\Services\Contabilidad\CalculoCosteo
 */
class CosteoController extends Controller
{
    /** Cuadro comparativo de todos los costeos */
    public function index()
    {
        $costeos = Costeo::with('elementos')->orderBy('nombre')->get();
        $resumenes = $costeos->mapWithKeys(fn (Costeo $c) => [$c->id => $c->resumen()]);

        // La alternativa con el menor costo por unidad (si hay al menos 2 con elementos)
        $conCosto = $resumenes->filter(fn ($r) => $r['elementos'] > 0);
        $masBarato = $conCosto->count() >= 2 ? $conCosto->sortBy('unitario')->keys()->first() : null;

        return view('contabilidad.costeos.index', [
            'costeos' => $costeos,
            'resumenes' => $resumenes,
            'masBarato' => $masBarato,
            'elementos' => ElementoCosto::cases(),
        ]);
    }

    /** Boton "Cargar ejemplo provisional" */
    public function cargarEjemplo()
    {
        $n = CosteosProvisionales::cargar();

        return redirect()->route('contabilidad.costeos.index')
            ->with($n ? 'success' : 'info', $n ? "Se cargaron $n costeos provisionales (estantes). Reemplácenlos con los datos reales." : 'El ejemplo ya estaba cargado.');
    }

    public function create()
    {
        return view('contabilidad.costeos.form', ['costeo' => new Costeo(['unidad' => 'metro lineal de exhibición']), 'sistemas' => SistemaCosteo::cases()]);
    }

    public function store(CosteoRequest $request)
    {
        $costeo = Costeo::create($request->validated());

        return redirect()->route('contabilidad.costeos.show', $costeo)
            ->with('success', 'Costeo creado. Ahora agrega sus elementos de costo.');
    }

    public function show(Costeo $costeo)
    {
        $costeo->load(['elementos' => fn ($q) => $q->with('cuenta')->withCount('evidencias')]);

        return view('contabilidad.costeos.show', [
            'costeo' => $costeo,
            'resumen' => $costeo->resumen(),
            'elemento' => new CosteoElemento(['comportamiento' => 'fijo', 'estimado' => true]),
            'cuentas' => $this->cuentas(),
        ]);
    }

    public function edit(Costeo $costeo)
    {
        return view('contabilidad.costeos.form', ['costeo' => $costeo, 'sistemas' => SistemaCosteo::cases()]);
    }

    public function update(CosteoRequest $request, Costeo $costeo)
    {
        $costeo->update($request->validated());

        return redirect()->route('contabilidad.costeos.show', $costeo)->with('success', 'Costeo actualizado.');
    }

    public function destroy(Costeo $costeo)
    {
        // Primero cada costo (asi se quitan tambien sus vinculos de evidencias) y luego el costeo
        DB::transaction(function () use ($costeo) {
            $costeo->elementos->each->delete();
            $costeo->delete();
        });

        return redirect()->route('contabilidad.costeos.index')->with('success', "Se eliminó \"{$costeo->nombre}\".");
    }

    // ---------- Elementos del costeo ----------

    public function guardarElemento(CosteoElementoRequest $request, Costeo $costeo)
    {
        $costeo->elementos()->create($request->validated());

        return redirect()->route('contabilidad.costeos.show', $costeo)->with('success', 'Elemento de costo agregado.');
    }

    public function editarElemento(CosteoElemento $elemento)
    {
        return view('contabilidad.costeos.elemento', [
            'elemento' => $elemento->load('costeo'),
            'cuentas' => $this->cuentas(),
        ]);
    }

    public function actualizarElemento(CosteoElementoRequest $request, CosteoElemento $elemento)
    {
        $elemento->update($request->validated());

        return redirect()->route('contabilidad.costeos.show', $elemento->costeo_id)->with('success', 'Elemento de costo actualizado.');
    }

    public function eliminarElemento(CosteoElemento $elemento)
    {
        $elemento->delete();

        return redirect()->route('contabilidad.costeos.show', $elemento->costeo_id)->with('success', 'Elemento de costo eliminado.');
    }

    /** Cuentas de detalle activas, para enlazar un costo con su cuenta del mayor */
    private function cuentas()
    {
        return Cuenta::query()->deDetalle()->where('activa', true)->orderBy('codigo')->get();
    }
}