<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Economia\AlternativaRequest;
use App\Models\Alternativa;
use App\Models\Costeo;
use App\Models\Decision;
use App\Support\EconomiaProvisional;

class AlternativaController extends Controller
{
    /** Muestra todas las alternativas registradas. */
    public function index()
    {
        $decision = Decision::actual();

        $alternativas = $decision
            ? $decision->alternativas()
                ->with('costeo.elementos')
                ->withCount('evidencias')
                ->get()
            : collect();

        return view(
            'economia.alternativas.index',
            compact('decision', 'alternativas')
        );
    }

    /** Carga los datos provisionales de prueba. */
    public function cargarEjemplo()
    {
        $n = EconomiaProvisional::cargar();

        return redirect()
            ->route('economia.alternativas.index')
            ->with(
                $n ? 'success' : 'info',
                $n
                    ? "Se cargaron $n alternativas provisionales. Reemplácenlas con la propuesta real."
                    : 'El ejemplo ya estaba cargado.'
            );
    }

    /** Muestra el formulario de una nueva alternativa. */
    public function create()
    {
        if (! Decision::actual()) {
            return $this->sinDecision();
        }

        return view('economia.alternativas.form', [
            'alternativa' => new Alternativa,
            'costeos' => $this->costeos()
        ]);
    }

    /** Guarda una nueva alternativa. */
    public function store(AlternativaRequest $request)
    {
        if (! $decision = Decision::actual()) {
            return $this->sinDecision();
        }

        $alternativa = $decision->alternativas()
            ->create($request->validated());

        return redirect()
            ->route('economia.alternativas.show', $alternativa)
            ->with('success', 'Alternativa registrada. Sube sus evidencias abajo.');
    }

    /** Muestra el detalle de una alternativa. */
    public function show(Alternativa $alternativa)
    {
        $alternativa->load([
            'decision',
            'costeo.elementos',
            'evidencias'
        ]);

        return view(
            'economia.alternativas.show',
            compact('alternativa')
        );
    }

    /** Muestra el formulario de edición. */
    public function edit(Alternativa $alternativa)
    {
        return view('economia.alternativas.form', [
            'alternativa' => $alternativa,
            'costeos' => $this->costeos()
        ]);
    }

    /** Actualiza una alternativa existente. */
    public function update(
        AlternativaRequest $request,
        Alternativa $alternativa
    )
    {
        $alternativa->update($request->validated());

        return redirect()
            ->route('economia.alternativas.show', $alternativa)
            ->with('success', 'Alternativa actualizada.');
    }

    /** Elimina una alternativa sin eliminar su costeo. */
    public function destroy(Alternativa $alternativa)
    {
        $alternativa->delete();

        return redirect()
            ->route('economia.alternativas.index')
            ->with(
                'success',
                "Se eliminó \"{$alternativa->nombre}\"."
            );
    }

    /** Recupera los costeos registrados en Contabilidad. */
    private function costeos()
    {
        return Costeo::query()
            ->with('elementos')
            ->orderBy('nombre')
            ->get();
    }

    /** Redirige si todavía no existe una decisión. */
    private function sinDecision()
    {
        return redirect()
            ->route('economia.decision.edit')
            ->with(
                'warning',
                'Primero registra la decisión de inversión (TMAR e ISR).'
            );
    }
}

