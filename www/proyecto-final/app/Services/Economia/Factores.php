<?php

namespace App\Services\Economia;

/**
 * Factores de interés compuesto.
 * La tasa i se recibe como decimal: 15 % = 0.15.
 */
class Factores
{
    /** Factor F/P */
    public static function fp(float $i, int $n): float
    {
        return (1 + $i) ** $n;
    }

    /** Factor P/F */
    public static function pf(float $i, int $n): float
    {
        return (1 + $i) ** -$n;
    }

    /** Factor P/A */
    public static function pa(float $i, int $n): float
    {
        return $i == 0.0 ? $n : (1 - (1 + $i) ** -$n) / $i;
    }

    /** Factor A/P */
    public static function ap(float $i, int $n): float
    {
        return 1 / self::pa($i, $n);
    }

    /** Factor F/A */
    public static function fa(float $i, int $n): float
    {
        return $i == 0.0 ? $n : ((1 + $i) ** $n - 1) / $i;
    }

    /** Factor A/F */
    public static function af(float $i, int $n): float
    {
        return 1 / self::fa($i, $n);
    }

    /** Devuelve los seis factores. */
    public static function todos(float $i, int $n): array
    {
        return [
            'fp' => self::fp($i, $n),
            'pf' => self::pf($i, $n),
            'fa' => self::fa($i, $n),
            'af' => self::af($i, $n),
            'pa' => self::pa($i, $n),
            'ap' => self::ap($i, $n),
        ];
    }

    /** Convierte una tasa nominal a efectiva anual. */
    public static function efectiva(float $nominal, int $m): float
    {
        return (1 + $nominal / $m) ** $m - 1;
    }

    public const NOMBRES = [
        'fp' => ['F/P', 'Cantidad compuesta, pago único', '(1 + i)ⁿ'],
        'pf' => ['P/F', 'Valor presente, pago único', '(1 + i)⁻ⁿ'],
        'fa' => ['F/A', 'Cantidad compuesta, serie uniforme', '[(1 + i)ⁿ − 1] / i'],
        'af' => ['A/F', 'Fondo de amortización', 'i / [(1 + i)ⁿ − 1]'],
        'pa' => ['P/A', 'Valor presente, serie uniforme', '[1 − (1 + i)⁻ⁿ] / i'],
        'ap' => ['A/P', 'Recuperación de capital', 'i(1 + i)ⁿ / [(1 + i)ⁿ − 1]'],
    ];
}
