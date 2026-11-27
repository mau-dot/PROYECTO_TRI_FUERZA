<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Economia;
use App\Http\Controllers\EvidenciaController;

/*
| Rutas de login, registro, logout y recuperacion de contraseña:
| las crea Fortify automaticamente (ver con: php artisan route:list).
*/

Route::view('/', 'home.index')->name('home');

Route::middleware('auth')->group(function () {
    Route::view('/usuarios/perfil', 'usuarios.perfil')->name('perfil');

    // ===== MÓDULO DE CONTABILIDAD =====
    Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
        Route::get('/', Contabilidad\DashboardController::class)->name('dashboard');
        Route::get('empresa', [Contabilidad\EmpresaController::class, 'edit'])->name('empresa.edit');
        Route::put('empresa', [Contabilidad\EmpresaController::class, 'update'])->name('empresa.update');

        Route::resource('periodos', Contabilidad\PeriodoController::class)->except('show');
        Route::post('periodos/{periodo}/activar', [Contabilidad\PeriodoController::class, 'activar'])->name('periodos.activar');
        Route::patch('periodos/{periodo}/estado', [Contabilidad\PeriodoController::class, 'cambiarEstado'])->name('periodos.estado');

        
        //  catalogo de cuentas
        Route::post('cuentas/cargar-base', [Contabilidad\CuentaController::class, 'cargarBase'])->name('cuentas.cargar-base');
        Route::patch('cuentas/{cuenta}/estado', [Contabilidad\CuentaController::class, 'cambiarEstado'])->name('cuentas.estado');
        Route::resource('cuentas', Contabilidad\CuentaController::class)->except('show');

        //  libro diario y asientos de ajuste
        Route::get('ajustes', [Contabilidad\PartidaController::class, 'ajustes'])->name('ajustes.index');
        Route::resource('partidas', Contabilidad\PartidaController::class);

        
        // reportes contables
        Route::get('mayor', [Contabilidad\ReporteController::class, 'mayor'])->name('mayor');
        Route::get('balanza', [Contabilidad\ReporteController::class, 'balanza'])->name('balanza');
        Route::get('hoja-trabajo', [Contabilidad\ReporteController::class, 'hojaTrabajo'])->name('hoja-trabajo');
        Route::get('estados-financieros', [Contabilidad\ReporteController::class, 'estados'])->name('estados');

        
        // sistema de costos
        Route::post('costeos/cargar-ejemplo', [Contabilidad\CosteoController::class, 'cargarEjemplo'])->name('costeos.cargar-ejemplo');
        Route::post('costeos/{costeo}/elementos', [Contabilidad\CosteoController::class, 'guardarElemento'])->name('costeos.elementos.store');
        Route::get('costeos/elementos/{elemento}/editar', [Contabilidad\CosteoController::class, 'editarElemento'])->name('costeos.elementos.edit');
        Route::put('costeos/elementos/{elemento}', [Contabilidad\CosteoController::class, 'actualizarElemento'])->name('costeos.elementos.update');
        Route::delete('costeos/elementos/{elemento}', [Contabilidad\CosteoController::class, 'eliminarElemento'])->name('costeos.elementos.destroy');
        Route::resource('costeos', Contabilidad\CosteoController::class);

    });

    // ===== EVIDENCIAS (compartido por los dos modulos) =====
    Route::post('evidencias/vincular', [EvidenciaController::class, 'vincular'])->name('evidencias.vincular');
    Route::delete('evidencias/vinculos/{vinculo}', [EvidenciaController::class, 'desvincular'])->name('evidencias.desvincular');
    Route::get('evidencias/{evidencia}/archivo', [EvidenciaController::class, 'archivo'])->name('evidencias.archivo');
    Route::resource('evidencias', EvidenciaController::class)->parameters(['evidencias' => 'evidencia']);


    // ===== MÓDULO DE ECONOMÍA =====
        Route::prefix('economia')->name('economia.')->group(function () {

        Route::get('/', Economia\DashboardController::class)->name('dashboard');

        // Decisión de inversión
        Route::get('decision', [Economia\DecisionController::class, 'edit'])->name('decision.edit');
        Route::put('decision', [Economia\DecisionController::class, 'update'])->name('decision.update');

        // Alternativas de inversión
        Route::post('alternativas/cargar-ejemplo', [Economia\AlternativaController::class, 'cargarEjemplo'])->name('alternativas.cargar-ejemplo');
        Route::resource('alternativas', Economia\AlternativaController::class);

        // Flujos de caja
        Route::get('flujos', [Economia\CalculoController::class, 'flujos'])->name('flujos.index');

        // Factores y equivalencias
        Route::get('factores', [Economia\CalculoController::class, 'factores'])->name('factores');

        // Depreciación
        Route::get('depreciacion', [Economia\CalculoController::class, 'depreciacion'])->name('depreciacion');


        // ===== FASE 7: EVALUACIÓN ECONÓMICA =====
        // Indicadores económicos antes de impuestos.
        Route::get('indicadores', [Economia\EvaluacionController::class, 'indicadores'])->name('indicadores');
        // Evaluación después de impuestos.
        Route::get('impuestos', [Economia\EvaluacionController::class, 'impuestos'])->name('impuestos');
        // Determinación de la vida económica.
        Route::get('vida-economica', [Economia\EvaluacionController::class, 'vidaEconomica'])->name('vida-economica');
        // Análisis de retiro y reemplazo.
        Route::get('reemplazo', [Economia\EvaluacionController::class, 'reemplazo'])->name('reemplazo');
        // Comparación de alternativas y análisis incremental.
        Route::get('comparacion', [Economia\EvaluacionController::class, 'comparacion'])->name('comparacion');
    });
});