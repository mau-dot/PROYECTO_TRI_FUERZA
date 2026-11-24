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

    });

    // ===== EVIDENCIAS (compartido por los dos modulos) =====
    Route::post('evidencias/vincular', [EvidenciaController::class, 'vincular'])->name('evidencias.vincular');
    Route::delete('evidencias/vinculos/{vinculo}', [EvidenciaController::class, 'desvincular'])->name('evidencias.desvincular');
    Route::get('evidencias/{evidencia}/archivo', [EvidenciaController::class, 'archivo'])->name('evidencias.archivo');
    Route::resource('evidencias', EvidenciaController::class)->parameters(['evidencias' => 'evidencia']);


    // ===== MÓDULO DE ECONOMÍA =====
    Route::prefix('economia')->name('economia.')->group(function () {
        Route::get('/', Economia\DashboardController::class)->name('dashboard');
    });
});