<?php

namespace App\Http\Responses;

use Filament\Auth\Http\Responses\Contracts\LogoutResponse;
use Illuminate\Http\RedirectResponse;

/**
 * Al cerrar sesión desde el panel, volver al inicio del sitio
 * (igual que el botón "Cerrar sesión" de Fortify).
 */
class FilamentLogoutResponse implements LogoutResponse
{
    public function toResponse($request): RedirectResponse
    {
        return redirect()->route('home');
    }
}