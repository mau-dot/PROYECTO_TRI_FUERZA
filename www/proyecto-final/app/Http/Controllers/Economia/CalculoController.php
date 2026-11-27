<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\Services\Economia\Depreciacion;
use App\Services\Economia\Factores;
use App\Services\Economia\FlujosCaja;
use Illuminate\Http\Request;

/**
 * Controlador de cálculos económicos de la fase 6.
 * Los resultados se calculan sin guardarlos en tablas.
 */
class CalculoController extends Controller
{
    /** Genera los flujos de caja. */
    public function flujos()
    {
        if (! $decision = Decision::actual()) {
            return $this->sinDecision();
        }

        $alternativas = $decision->alternativas()
            ->with('costeo.elementos')
            ->get();

        $flujos = $alternativas->mapWithKeys(
            fn ($a) => [$a->id => FlujosCaja::generar($a)]
        );

        return view(
            'economia.reportes.flujos',
            compact('decision', 'alternativas', 'flujos')
        );
    }

    /** Genera los factores de interés compuesto. */
    public function factores(Request $request)
    {
        $decision = Decision::actual();

        $maxVida = (int) (
            $decision?->alternativas()->max('vida_util') ?: 10
        );

        $tasa = (float) (
            $request->query('i') ?: ($decision?->tmar ?? 10)
        );

        $tasa = min(max($tasa, 0.01), 100);

        $hasta = min(
            max(
                (int) ($request->query('n') ?: $maxVida),
                1
            ),
            60
        );

        $filas = [];

        for ($n = 1; $n <= $hasta; $n++) {
            $filas[$n] = Factores::todos($tasa / 100, $n);
        }

        return view(
            'economia.reportes.factores',
            compact('decision', 'tasa', 'hasta', 'filas')
        );
    }

    /** Genera los tres métodos de depreciación. */
    public function depreciacion(Request $request)
    {
        if (! $decision = Decision::actual()) {
            return $this->sinDecision();
        }

        $alternativas = $decision->alternativas;

        $alternativa = $alternativas->firstWhere(
            'id',
            $request->integer('alternativa')
        ) ?? $alternativas->first();

        $tablas = $alternativa
            ? Depreciacion::todas(
                (float) $alternativa->inversion,
                (float) $alternativa->valor_salvamento,
                $alternativa->vida_util
            )
            : [];

        return view(
            'economia.reportes.depreciacion',
            compact(
                'decision',
                'alternativas',
                'alternativa',
                'tablas'
            )
        );
    }

    /** Redirige si no se ha registrado la decisión. */
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
