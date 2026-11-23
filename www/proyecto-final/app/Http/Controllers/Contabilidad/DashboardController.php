<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Periodo;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $empresa = Empresa::actual();
        $periodo = Periodo::activo();

        $avance = [
            ['texto' => 'Empresa registrada y clasificada', 'hecho' => (bool) $empresa, 'fase' => 1],
            ['texto' => 'Periodo contable creado', 'hecho' => (bool) $periodo, 'fase' => 1],
            ['texto' => 'Catálogo de cuentas', 'hecho' => false, 'fase' => 2],
            ['texto' => 'Partidas del libro diario (partida doble)', 'hecho' => false, 'fase' => 2],
            ['texto' => 'Evidencias de los datos (mínimo 2 fuentes)', 'hecho' => false, 'fase' => 3],
            ['texto' => 'Mayor, balanza, ajustes y hoja de trabajo', 'hecho' => false, 'fase' => 4],
            ['texto' => 'Estados financieros', 'hecho' => false, 'fase' => 4],
            ['texto' => 'Sistema de costos por alternativa', 'hecho' => false, 'fase' => 5],
        ];

        return view('contabilidad.dashboard', compact('empresa', 'periodo', 'avance'));
    }
}