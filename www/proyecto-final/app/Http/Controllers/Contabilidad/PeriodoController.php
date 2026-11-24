<?php

namespace App\Http\Controllers\Contabilidad;

use App\Enums\EstadoPeriodo;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contabilidad\PeriodoRequest;
use App\Models\Empresa;
use App\Models\Periodo;

class PeriodoController extends Controller
{
    public function index()
    {
        return view('contabilidad.periodos.index', [
            'periodos' => Periodo::query()->latest('fecha_inicio')->get(),
            'activo' => Periodo::activo(),
            'hayEmpresa' => (bool) Empresa::actual(),
        ]);
    }

    public function create()
    {
        if (! Empresa::actual()) {
            return $this->sinEmpresa();
        }

        return view('contabilidad.periodos.form', ['periodo' => new Periodo]);
    }

    public function store(PeriodoRequest $request)
    {
        $empresa = Empresa::actual();

        if (! $empresa) {
            return $this->sinEmpresa();
        }

        $periodo = $empresa->periodos()->create($request->validated());
        session([Periodo::SESION => $periodo->id]); // el recien creado pasa a ser el activo

        return redirect()->route('contabilidad.periodos.index')
            ->with('success', "Periodo \"{$periodo->nombre}\" creado y seleccionado como periodo activo.");
    }

    public function edit(Periodo $periodo)
    {
        if (! $periodo->estaAbierto()) {
            return $this->cerrado($periodo);
        }

        return view('contabilidad.periodos.form', compact('periodo'));
    }

    public function update(PeriodoRequest $request, Periodo $periodo)
    {
        if (! $periodo->estaAbierto()) {
            return $this->cerrado($periodo);
        }

        $periodo->update($request->validated());

        return redirect()->route('contabilidad.periodos.index')->with('success', 'Periodo actualizado.');
    }

    public function destroy(Periodo $periodo)
    {
        if (! $periodo->estaAbierto()) {
            return $this->cerrado($periodo);
        }

        // aqui se impedira borrar un periodo que ya tenga partidas.
        $periodo->delete();

        if (session(Periodo::SESION) == $periodo->id) {
            session()->forget(Periodo::SESION);
        }

        return redirect()->route('contabilidad.periodos.index')->with('success', "Periodo \"{$periodo->nombre}\" eliminado.");
    }

    /** Elegir con que periodo se trabaja (se guarda en la sesion del usuario) */
    public function activar(Periodo $periodo)
    {
        session([Periodo::SESION => $periodo->id]);

        return back()->with('success', "Ahora trabajas en el periodo \"{$periodo->nombre}\".");
    }

    /** Cerrar un periodo abierto o reabrir uno cerrado */
    public function cambiarEstado(Periodo $periodo)
    {
        $periodo->estado = $periodo->estaAbierto() ? EstadoPeriodo::Cerrado : EstadoPeriodo::Abierto;
        $periodo->save();

        $accion = $periodo->estaAbierto() ? 'reabierto' : 'cerrado';

        return back()->with('success', "Periodo \"{$periodo->nombre}\" {$accion}.");
    }

    private function sinEmpresa()
    {
        return redirect()->route('contabilidad.empresa.edit')
            ->with('warning', 'Antes de crear un periodo registra los datos de la empresa.');
    }

    private function cerrado(Periodo $periodo)
    {
        return redirect()->route('contabilidad.periodos.index')
            ->with('error', "El periodo \"{$periodo->nombre}\" está cerrado. Reábrelo para modificarlo.");
    }
}