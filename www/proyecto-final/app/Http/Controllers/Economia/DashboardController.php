<?php

namespace App\Http\Controllers\Economia;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Periodo;
use App\Models\Decision;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $empresa = Empresa::actual();
        $periodo = Periodo::activo();
        // Fase 6: datos del componente económico
        $decision = Decision::actual();
        $numAlternativas = $decision
            ? $decision->alternativas()->count()
            : 0;

        // Avance del componente económico 
        $avance = [
            ['texto' => 'Datos contables del periodo (módulo de Contabilidad)', 'hecho' => (bool) $periodo, 'fase' => 1],
            ['texto' => 'Decisión de inversión, TMAR y tasa de ISR', 'hecho' => (bool) $decision, 'fase' => 6],
            ['texto' => 'Alternativas y flujos de caja', 'hecho' => $numAlternativas >= 2, 'fase' => 6],
            ['texto' => 'Depreciación (mínimo 2 métodos)', 'hecho' => $numAlternativas >= 2, 'fase' => 6],
            ['texto' => 'VPN, TIR, CAUE y B/C por alternativa', 'hecho' => false, 'fase' => 7],
            ['texto' => 'Vida económica, retiro y reemplazo', 'hecho' => false, 'fase' => 7],
            ['texto' => 'Comparación y alternativa recomendada', 'hecho' => false, 'fase' => 7],
            ['texto' => 'Verificación contra el cálculo manual', 'hecho' => false, 'fase' => 8],
        ];

        return view('economia.dashboard', compact('empresa', 'periodo', 'avance', 'decision', 'numAlternativas'));
    }
}
