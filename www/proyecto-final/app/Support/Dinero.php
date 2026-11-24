<?php

namespace App\Support;

/**
 * Dinero en CENTAVOS (enteros). Sumar y restar enteros es exacto; con float no
 * (0.1 + 0.2 = 0.30000000000000004). Todos los reportes trabajan en centavos y
 * solo se convierten a "1,234.56" al mostrarlos.
 */
class Dinero
{
    /** "1234.5" o "-0.30" (lo que devuelve PostgreSQL en un SUM) -> 123450 / -30, sin pasar por float */
    public static function centavos(mixed $valor): int
    {
        if ($valor === null || $valor === '') {
            return 0;
        }
        if (is_int($valor)) {
            return $valor * 100;
        }

        $texto = trim((string) $valor);
        $negativo = str_starts_with($texto, '-');
        $texto = ltrim($texto, '+-');
        [$enteros, $decimales] = array_pad(explode('.', $texto, 2), 2, '0');
        $decimales = substr(str_pad($decimales, 3, '0'), 0, 3);   // 3 dígitos para redondear el tercero

        $centavos = ((int) $enteros) * 100 + intdiv((int) $decimales + 5, 10);

        return $negativo ? -$centavos : $centavos;
    }

    /** 123450 -> "1,234.50" ; -15000 -> "(150.00)" (negativo entre parentesis, como en contabilidad) */
    public static function formato(int $centavos, bool $parentesis = true): string
    {
        $texto = number_format(abs($centavos) / 100, 2);

        if ($centavos < 0) {
            return $parentesis ? "($texto)" : "-$texto";
        }

        return $texto;
    }

    /** Igual que formato() pero deja vacio el cero (para columnas de debe/haber) */
    public static function celda(int $centavos): string
    {
        return $centavos === 0 ? '' : self::formato($centavos);
    }
}