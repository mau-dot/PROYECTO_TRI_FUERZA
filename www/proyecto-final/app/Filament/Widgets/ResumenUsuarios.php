<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/**
 * Tarjetas del escritorio de /admin. Aquí se irán agregando
 * los totales de contabilidad y economía (cuentas, partidas, alternativas...).
 */
class ResumenUsuarios extends StatsOverviewWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        return [
            Stat::make('Usuarios registrados', User::count())
                ->icon('heroicon-o-users'),
            Stat::make('Administradores', User::where('is_admin', true)->count())
                ->icon('heroicon-o-shield-check'),
            Stat::make('Nuevos en los últimos 7 días', User::where('created_at', '>=', now()->subDays(7))->count())
                ->icon('heroicon-o-user-plus'),
        ];
    }
}