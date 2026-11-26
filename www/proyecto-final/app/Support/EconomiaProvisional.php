<?php

namespace App\Support;

use App\Models\Costeo;
use App\Models\Decision;
use Illuminate\Support\Facades\DB;

/**
 * Datos provisionales de Economía.
 * Se utilizan únicamente para probar el sistema
 * mientras se consiguen los datos reales.
 */
class EconomiaProvisional
{
    public const ALTERNATIVAS = [
        // Nombre, inversión, vida útil, salvamento, beneficio anual
        ['Alternativa A: estantes metálicos nuevos', 1800, 10, 200, 1000],
        ['Alternativa B: estantes metálicos usados', 900, 5, 50, 850],
        ['Alternativa C: estantes de madera a la medida', 1200, 7, 100, 950],
    ];

    public static function cargar(): int
    {
        return DB::transaction(function () {

            CosteosProvisionales::cargar();

            $decision = Decision::actual() ?? Decision::create([
                'titulo' => 'Renovar la estantería de la tienda',
                'descripcion' => 'Decisión SUPUESTA mientras llega la propuesta real. TMAR: tasa de préstamo pyme + prima de riesgo (supuesta). ISR: 25 % (ingresos menores a $150,000; verificar).',
                'tmar' => 15,
                'tasa_isr' => 25,
                'provisional' => true,
            ]);

            $creadas = 0;

            foreach (self::ALTERNATIVAS as [
                $nombre,
                $inversion,
                $vida,
                $salvamento,
                $beneficio
            ]) {

                if ($decision->alternativas()
                    ->where('nombre', $nombre)->exists()) {
                    continue;
                }

                $costeo = Costeo::where('nombre', $nombre)->first();

                $decision->alternativas()->create([
                    'nombre' => $nombre,
                    'descripcion' => 'Datos supuestos. Beneficio = margen bruto adicional por mejor exhibición (supuesto).',
                    'inversion' => $inversion,
                    'vida_util' => $vida,
                    'valor_salvamento' => $salvamento,
                    'beneficio_anual' => $beneficio,
                    'costeo_id' => $costeo?->id,
                    'provisional' => true,
                ]);

                $creadas++;
            }

            return $creadas;
        });
    }
}
