<?php

namespace App\Http\Controllers\Contabilidad;

use App\Enums\ClasificacionEmpresa;
use App\Enums\TamanoEmpresa;
use App\Http\Controllers\Controller;
use App\Http\Requests\Contabilidad\EmpresaRequest;
use App\Models\Empresa;

/**
 * Hay una sola empresa: la misma pantalla sirve para registrarla y para editarla.
 */
class EmpresaController extends Controller
{
    public function edit()
    {
        return view('contabilidad.empresa.edit', [
            'empresa' => Empresa::actual() ?? new Empresa(['tasa_iva' => 13]),
            'clasificaciones' => ClasificacionEmpresa::cases(),
            'tamanos' => TamanoEmpresa::cases(),
        ]);
    }

    public function update(EmpresaRequest $request)
    {
        $empresa = Empresa::actual() ?? new Empresa;
        $nueva = ! $empresa->exists;

        $empresa->fill($request->validated())->save();

        return $nueva
            ? redirect()->route('contabilidad.periodos.create')->with('success', 'Empresa registrada. Ahora crea el periodo contable que se va a evaluar.')
            : redirect()->route('contabilidad.empresa.edit')->with('success', 'Datos de la empresa actualizados.');
    }
}