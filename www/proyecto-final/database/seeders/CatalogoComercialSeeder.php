<?php

namespace Database\Seeders;

use App\Support\CatalogoBaseComercial;
use Illuminate\Database\Seeder;

/**
 * Uso: lv php artisan db:seed --class=CatalogoComercialSeeder
 * (tambien se puede cargar con el boton de la pantalla "Catalogo de cuentas")
 */
class CatalogoComercialSeeder extends Seeder
{
    public function run(): void
    {
        $creadas = CatalogoBaseComercial::cargar();
        $this->command?->info("Catálogo base comercial: {$creadas} cuentas creadas.");
    }
}