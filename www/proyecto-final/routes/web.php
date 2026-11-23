<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Contabilidad;
use App\Http\Controllers\Economia;

/*
| Rutas de login, registro, logout y recuperacion de contraseña:
| las crea Fortify automaticamente (ver con: php artisan route:list).
*/

Route::view('/', 'home.index')->name('home');

Route::middleware('auth')->group(function () {
    Route::view('/usuarios/perfil', 'usuarios.perfil')->name('perfil');
});



// ===== MÓDULO DE CONTABILIDAD =====
Route::prefix('contabilidad')->name('contabilidad.')->group(function () {
    Route::get('/', Contabilidad\DashboardController::class)->name('dashboard');
    Route::get('empresa', [Contabilidad\EmpresaController::class, 'edit'])->name('empresa.edit');
    Route::put('empresa', [Contabilidad\EmpresaController::class, 'update'])->name('empresa.update');

    Route::resource('periodos', Contabilidad\PeriodoController::class)->except('show');
    Route::post('periodos/{periodo}/activar', [Contabilidad\PeriodoController::class, 'activar'])->name('periodos.activar');
    Route::patch('periodos/{periodo}/estado', [Contabilidad\PeriodoController::class, 'cambiarEstado'])->name('periodos.estado');
});

// ===== MÓDULO DE ECONOMÍA =====
Route::prefix('economia')->name('economia.')->group(function () {
    Route::get('/', Economia\DashboardController::class)->name('dashboard');
});