<?php

namespace App\Services\Economia;

/**
 * Cuadros de depreciación mediante tres métodos.
 */
class Depreciacion
{
    public const METODOS = [
        'lr' => 'Línea recta',
        'sda' => 'Suma de dígitos de los años',
        'sdd' => 'Saldo doble decreciente',
    ];

    public static function tabla(
        string $metodo,
        float $p,
        float $vs,
        int $n
    ): array
    {
        $depreciable = $p - $vs;
        $suma = $n * ($n + 1) / 2;
        $libros = $p;
        $acumulada = 0.0;
        $filas = [];

        for ($t = 1; $t <= $n; $t++) {
            $d = match ($metodo) {
                'lr' => $depreciable / $n,
                'sda' => $depreciable * ($n - $t + 1) / $suma,
                'sdd' => min($libros * 2 / $n, $libros - $vs),
            };

            $d = $t === $n
                ? $libros - $vs
                : round($d, 2);

            $d = max(round($d, 2), 0.0);

            $acumulada = round($acumulada + $d, 2);
            $libros = round($p - $acumulada, 2);

            $filas[] = [
                'anio' => $t,
                'depreciacion' => $d,
                'acumulada' => $acumulada,
                'libros' => $libros,
            ];
        }

        return $filas;
    }

    /** Calcula los tres métodos de depreciación. */
    public static function todas(
        float $p,
        float $vs,
        int $n
    ): array
    {
        $tablas = [];

        foreach (array_keys(self::METODOS) as $m) {
            $tablas[$m] = self::tabla($m, $p, $vs, $n);
        }

        return $tablas;
    }
}
