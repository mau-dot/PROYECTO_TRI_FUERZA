<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Decision;
use App\Models\Empresa;
use App\Models\Periodo;
use App\Services\Economia\Indicadores;
use App\Services\Economia\EvaluacionFiscal;
use App\Services\Economia\Depreciacion;
use App\Services\Economia\VidaEconomica;
use App\Services\Economia\Reemplazo;
use App\Services\Economia\Comparacion;
use Illuminate\Http\Request;

class EvaluacionController extends Controller
{
    /**
     * VPN, TIR, CAUE y relacion beneficio/costo.
     */
    public function indicadores()
    {
        $decision = Decision::actual();

        if (! $decision) {
            return redirect()
                ->route('economia.decision.edit')
                ->with('warning', 'Registra primero la decision de inversion.');
        }

        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        $alternativas = $decision->alternativas()->get();

        $resultados = $alternativas->map(
            fn ($a) => [
                'alternativa' => $a,
                'calculos' => Indicadores::evaluar(
                    $a,
                    $decision->i()
                ),
            ]
        );

        return view('economia.reportes.indicadores', compact(
            'empresa',
            'periodo',
            'decision',
            'alternativas',
            'resultados'
        ));
    }

    /**
     * Evaluacion despues de impuestos.
     */
    public function impuestos(Request $request)
    {
        $decision = Decision::actual();

        if (! $decision) {
            return redirect()
                ->route('economia.decision.edit');
        }

        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        $alternativas = $decision->alternativas()->get();

        if ($alternativas->isEmpty()) {
            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', 'Registra una alternativa.');
        }

        $request->validate([
            'alternativa' => ['nullable', 'integer'],
            'metodo' => ['nullable', 'in:lr,sda,sdd'],
            'escudo_fiscal' => ['nullable', 'boolean'],
        ]);

        $alternativa = $request->filled('alternativa')
            ? $alternativas->firstWhere(
                'id',
                (int) $request->input('alternativa')
            )
            : $alternativas->first();

        abort_unless($alternativa, 404);

        $metodo = $request->input('metodo', 'lr');

        $escudoFiscal = $request->boolean(
            'escudo_fiscal',
            true
        );

        $metodos = Depreciacion::METODOS;

        $resultado = EvaluacionFiscal::evaluar(
            $alternativa,
            $decision->i(),
            $decision->t(),
            $metodo,
            $escudoFiscal
        );

        $comparacionMetodos = EvaluacionFiscal::todos(
            $alternativa,
            $decision->i(),
            $decision->t(),
            $escudoFiscal
        );

        return view('economia.reportes.impuestos', compact(
            'empresa',
            'periodo',
            'decision',
            'alternativas',
            'alternativa',
            'metodo',
            'metodos',
            'escudoFiscal',
            'resultado',
            'comparacionMetodos'
        ));
    }

    /**
     * Calculo de la vida economica.
     */
    public function vidaEconomica(Request $request)
    {
        $decision = Decision::actual();

        if (! $decision) {
            return redirect()
                ->route('economia.decision.edit');
        }

        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        $alternativas = $decision->alternativas()->get();

        if ($alternativas->isEmpty()) {
            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', 'Registra una alternativa.');
        }

        $request->validate([
            'alternativa' => ['nullable', 'integer'],

            'perdida_valor' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],

            'crecimiento_costo' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],
        ]);

        $alternativa = $request->filled('alternativa')
            ? $alternativas->firstWhere(
                'id',
                (int) $request->input('alternativa')
            )
            : $alternativas->first();

        abort_unless($alternativa, 404);

        $perdidaValor = (float) $request->input(
            'perdida_valor',
            15
        );

        $crecimientoCosto = (float) $request->input(
            'crecimiento_costo',
            8
        );

        try {

            $resultado = VidaEconomica::evaluar(
                $alternativa,
                $decision->i(),
                $perdidaValor / 100,
                $crecimientoCosto / 100
            );

        } catch (\InvalidArgumentException $e) {

            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', $e->getMessage());
        }

        return view('economia.reportes.vida-economica', compact(
            'empresa',
            'periodo',
            'decision',
            'alternativas',
            'alternativa',
            'perdidaValor',
            'crecimientoCosto',
            'resultado'
        ));
    }

    /**
     * Analisis de retiro y reemplazo.
     */
    public function reemplazo(Request $request)
    {
        $decision = Decision::actual();

        if (! $decision) {
            return redirect()
                ->route('economia.decision.edit');
        }

        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        $alternativas = $decision->alternativas()->get();

        if ($alternativas->isEmpty()) {
            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', 'Registra una alternativa.');
        }

        $request->validate([
            'alternativa' => ['nullable', 'integer'],

            'valor_mercado' => [
                'nullable', 'numeric', 'gt:0'
            ],

            'costo_defensor' => [
                'nullable', 'numeric', 'min:0'
            ],

            'vida_restante' => [
                'nullable', 'integer', 'min:1', 'max:100'
            ],

            'perdida_defensor' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],

            'crecimiento_defensor' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],

            'perdida_retador' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],

            'crecimiento_retador' => [
                'nullable', 'numeric', 'min:0', 'max:100'
            ],
        ]);

        $alternativa = $request->filled('alternativa')
            ? $alternativas->firstWhere(
                'id',
                (int) $request->input('alternativa')
            )
            : $alternativas->first();

        abort_unless($alternativa, 404);

        // Valores iniciales provisionales.
        $valorMercado = (float) $request->input(
            'valor_mercado', 800
        );

        $costoDefensor = (float) $request->input(
            'costo_defensor', 1000
        );

        $vidaRestante = (int) $request->input(
            'vida_restante', 4
        );

        $perdidaDefensor = (float) $request->input(
            'perdida_defensor', 15
        );

        $crecimientoDefensor = (float) $request->input(
            'crecimiento_defensor', 8
        );

        $perdidaRetador = (float) $request->input(
            'perdida_retador', 15
        );

        $crecimientoRetador = (float) $request->input(
            'crecimiento_retador', 8
        );

        try {

            $resultado = Reemplazo::evaluar(
                $alternativa,
                $decision->i(),
                $valorMercado,
                $costoDefensor,
                $vidaRestante,
                $perdidaDefensor / 100,
                $crecimientoDefensor / 100,
                $perdidaRetador / 100,
                $crecimientoRetador / 100
            );

        } catch (\InvalidArgumentException $e) {

            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', $e->getMessage());
        }

        return view('economia.reportes.reemplazo', compact(
            'empresa',
            'periodo',
            'decision',
            'alternativas',
            'alternativa',
            'valorMercado',
            'costoDefensor',
            'vidaRestante',
            'perdidaDefensor',
            'crecimientoDefensor',
            'perdidaRetador',
            'crecimientoRetador',
            'resultado'
        ));
    }

    /**
     * Comparacion y analisis incremental.
     */
    public function comparacion(Request $request)
    {
        $decision = Decision::actual();

        if (! $decision) {
            return redirect()
                ->route('economia.decision.edit');
        }

        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        if (! $decision->alternativas()->exists()) {
            return redirect()
                ->route('economia.alternativas.index')
                ->with('warning', 'Registra una alternativa.');
        }

        $request->validate([
            'despues_impuestos' => ['nullable', 'boolean'],
            'metodo' => ['nullable', 'in:lr,sda,sdd'],
            'escudo_fiscal' => ['nullable', 'boolean'],
        ]);

        $despuesImpuestos = $request->boolean(
            'despues_impuestos',
            false
        );

        $metodo = $request->input('metodo', 'lr');

        $escudoFiscal = $request->boolean(
            'escudo_fiscal',
            true
        );

        $metodos = Depreciacion::METODOS;

        $resultado = Comparacion::evaluar(
            $decision,
            $despuesImpuestos,
            $metodo,
            $escudoFiscal
        );

        return view('economia.reportes.comparacion', compact(
            'empresa',
            'periodo',
            'decision',
            'despuesImpuestos',
            'metodo',
            'metodos',
            'escudoFiscal',
            'resultado'
        ));
    }
}

