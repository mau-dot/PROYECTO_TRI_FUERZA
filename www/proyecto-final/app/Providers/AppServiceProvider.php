<?php

namespace App\Providers;

use App\Http\Responses\FilamentLogoutResponse;
use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use App\Models\Cuenta;
use App\Models\Empresa;
use App\Models\Partida;
use App\Models\Costeo;
use App\Models\CosteoElemento;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // NUEVO (Filament): "Salir" en /admin vuelve al inicio del sitio
        $this->app->bind(LogoutResponse::class, FilamentLogoutResponse::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // (evidencias): nombres cortos para los datos que pueden tener evidencias.
        // En la tabla "evidenciables" se guarda 'partida' en vez de 'App\Models\Partida'.
        Relation::morphMap([
            'partida' => Partida::class,
            'empresa' => Empresa::class,
            'cuenta' => Cuenta::class,
            'costeo' => Costeo::class,                    
            'costeo_elemento' => CosteoElemento::class,   
        ]);
    }
}
