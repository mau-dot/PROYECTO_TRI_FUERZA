<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Http\Requests\Economia\DecisionRequest;
use App\Models\Decision;

/**
 * Controlador de la decisión de inversión.
 * Permite registrar y actualizar la decisión del caso.
 */
class DecisionController extends Controller
{
    public function edit()
    {
        return view('economia.decision.edit', [
            'decision' => Decision::actual() ?? new Decision([
                'tmar' => 15,
                'tasa_isr' => 25
            ]),
        ]);
    }

    public function update(DecisionRequest $request)
    {
        $decision = Decision::actual() ?? new Decision;

        $nueva = ! $decision->exists;

        $decision->fill($request->validated())->save();

        return $nueva
            ? redirect()
                ->route('economia.alternativas.index')
                ->with('success', 'Decisión registrada. Ahora agrega las alternativas.')
            : redirect()
                ->route('economia.decision.edit')
                ->with('success', 'Decisión actualizada.');
    }
}
