<?php

namespace App\Http\Controllers\Contabilidad;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Periodo;
use App\Services\Contabilidad\BalanzaComprobacion;
use App\Models\Cuenta;
use App\Models\Evidencia;
use App\Models\Costeo;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $empresa = Empresa::actual();
        $periodo = Periodo::activo();
        $numPartidas = $periodo ? $periodo->partidas()->count() : 0;
        $balanza = $periodo && $numPartidas ? BalanzaComprobacion::generar($periodo, true) : null;   

        $avance = [
            ['texto' => 'Empresa registrada y clasificada', 'hecho' => (bool) $empresa, 'fase' => 1],
            ['texto' => 'Periodo contable creado', 'hecho' => (bool) $periodo, 'fase' => 1],
            ['texto' => 'Catálogo de cuentas', 'hecho' => Cuenta::exists(), 'fase' => 2],
            ['texto' => 'Partidas del libro diario (partida doble)', 'hecho' => $numPartidas > 0, 'fase' => 2],
            ['texto' => 'Evidencias de los datos (mínimo 2 fuentes)', 'hecho' => Evidencia::exists(), 'fase' => 3],
            ['texto' => 'Mayor, balanza, ajustes y hoja de trabajo', 'hecho' => $balanza['cuadra'] ?? false, 'fase' => 4],
            ['texto' => 'Estados financieros', 'hecho' => $balanza['cuadra'] ?? false, 'fase' => 4],
            ['texto' => 'Sistema de costos por alternativa', 'hecho' => Costeo::count() >= 2, 'fase' => 5],
        ];

        return view('contabilidad.dashboard', compact('empresa', 'periodo', 'avance', 'numPartidas', 'balanza',));
    }
}