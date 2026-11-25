<?php

namespace App\Support;

use App\Models\Costeo;
use App\Models\Cuenta;
use Illuminate\Support\Facades\DB;

/**
 * DATOS PROVISIONALES (SUPUESTOS) SOLO PARA AVANZAR 
 * necesitamos la propuesta real de la tienda.
 *
 * Todo se marca como provisional / estimado, y se puede editar o borrar desde la pantalla.
 * Supuestos usados (cambienlos por los reales con sus evidencias):
 *   - Mano de obra: salario minimo de comercio $408.80 al mes ÷ 30 días ÷ 8 h ≈ $1.70 la hora.
 *   - Unidad de costeo: metro lineal de exhibición (permite comparar estantes de distinto tamaño).
 *   - La inversión inicial, la vida util y el salvamento NO van aqui
 */
class CosteosProvisionales
{
    public const ALTERNATIVAS = [
        [
            'nombre' => 'Alternativa A: estantes metálicos nuevos',
            'descripcion' => '6 góndolas metálicas nuevas de 2 m. Inversión supuesta: $1,800; vida útil supuesta: 10 años.',
            'unidad' => 'metro lineal de exhibición',
            'volumen_anual' => 12,
            'elementos' => [
                ['mod', 'fijo', 'Reposición y orden de mercadería en estantes', 208, 'horas', 1.70, '420201'],
                ['cif', 'fijo', 'Mantenimiento (pintura y tornillería)', 1, 'año', 40, null],
                ['cif', 'fijo', 'Limpieza e insumos de los estantes', 12, 'meses', 5, null],
                ['mpd', 'variable', 'Mercadería dañada por exhibición (merma)', 12, 'unidades', 3, '4101'],
            ],
        ],
        [
            'nombre' => 'Alternativa B: estantes metálicos usados',
            'descripcion' => '5 estantes metálicos usados de 2 m. Inversión supuesta: $900; vida útil supuesta: 5 años.',
            'unidad' => 'metro lineal de exhibición',
            'volumen_anual' => 10,
            'elementos' => [
                ['mod', 'fijo', 'Reposición y orden de mercadería en estantes', 260, 'horas', 1.70, '420201'],
                ['cif', 'fijo', 'Mantenimiento (soldadura, pintura y tornillería)', 1, 'año', 120, null],
                ['cif', 'fijo', 'Limpieza e insumos de los estantes', 12, 'meses', 5, null],
                ['mpd', 'variable', 'Mercadería dañada por exhibición (merma)', 36, 'unidades', 3, '4101'],
            ],
        ],
        [
            'nombre' => 'Alternativa C: estantes de madera a la medida',
            'descripcion' => 'Estantería de madera hecha por un carpintero, 12 m lineales. Inversión supuesta: $1,200; vida útil supuesta: 7 años.',
            'unidad' => 'metro lineal de exhibición',
            'volumen_anual' => 12,
            'elementos' => [
                ['mod', 'fijo', 'Reposición y orden de mercadería en estantes', 208, 'horas', 1.70, '420201'],
                ['cif', 'fijo', 'Mantenimiento (barniz y reparaciones)', 1, 'año', 90, null],
                ['cif', 'fijo', 'Limpieza e insumos de los estantes', 12, 'meses', 5, null],
                ['mpd', 'variable', 'Mercadería dañada por exhibición (merma)', 24, 'unidades', 3, '4101'],
            ],
        ],
    ];

    /**crea los costeos que falten (por nombre). Devuelve cuantos creo. */
    public static function cargar(): int
    {
        return DB::transaction(function () {
            $creados = 0;

            foreach (self::ALTERNATIVAS as $a) {
                if (Costeo::where('nombre', $a['nombre'])->exists()) {
                    continue;
                }

                $costeo = Costeo::create([
                    'nombre' => $a['nombre'], 'descripcion' => $a['descripcion'], 'sistema' => 'estandar',
                    'unidad' => $a['unidad'], 'volumen_anual' => $a['volumen_anual'], 'provisional' => true,
                ]);

                foreach ($a['elementos'] as [$elemento, $comportamiento, $concepto, $cantidad, $unidad, $costo, $codigo]) {
                    $costeo->elementos()->create([
                        'elemento' => $elemento, 'comportamiento' => $comportamiento, 'concepto' => $concepto,
                        'cantidad' => $cantidad, 'unidad_medida' => $unidad, 'costo_unitario' => $costo,
                        'cuenta_id' => $codigo ? Cuenta::where('codigo', $codigo)->value('id') : null,
                        'estimado' => true,
                    ]);
                }
                $creados++;
            }

            return $creados;
        });
    }
}