<?php

use Illuminate\Support\Facades\Route;

/*
| Rutas de login, registro, logout y recuperacion de contraseña:
| las crea Fortify automaticamente (ver con: php artisan route:list).
*/

Route::view('/', 'home.index')->name('home');

Route::middleware('auth')->group(function () {
    Route::view('/usuarios/perfil', 'usuarios.perfil')->name('perfil');
});