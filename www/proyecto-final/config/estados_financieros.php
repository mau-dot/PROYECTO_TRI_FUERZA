<?php

/*
|--------------------------------------------------------------------------
| Estado de resultados: que cuentas van en cada seccion
|--------------------------------------------------------------------------
| Cada seccion agrupa cuentas por el INICIO de su codigo (prefijo).
| Si una cuenta calza con dos prefijos, gana el mas largo
| (5103 calza con "5" de ingresos y con "5103" de costo: va a costo).
|
| 'naturaleza' dice como se lee el saldo de la seccion:
|   acreedora -> haber - debe (ingresos). Una cuenta deudora dentro
|                (5102 Rebajas sobre ventas) queda en negativo y resta sola.
|   deudora   -> debe - haber (costos y gastos). 5103 Rebajas sobre compras
|                es acreedora, queda negativa y resta del costo.
|
| 
*/

return [

    'resultados' => [
        'ventas' => ['titulo' => 'Ingresos por ventas', 'prefijos' => ['51'], 'naturaleza' => 'acreedora'],
        'costo' => ['titulo' => 'Costo de venta', 'prefijos' => ['41', '5103'], 'naturaleza' => 'deudora'],
        'gastos_operacion' => ['titulo' => 'Gastos de operación', 'prefijos' => ['42'], 'naturaleza' => 'deudora'],
        'otros_ingresos' => ['titulo' => 'Otros ingresos', 'prefijos' => ['52'], 'naturaleza' => 'acreedora'],
        'otros_gastos' => ['titulo' => 'Gastos no de operación', 'prefijos' => ['43'], 'naturaleza' => 'deudora'],
        'isr' => ['titulo' => 'Impuesto sobre la renta', 'prefijos' => ['44'], 'naturaleza' => 'deudora'],
    ],

    // Nivel de las cuentas que se muestran en el balance general (3 = "1101 Efectivo y equivalentes")
    'nivel_balance' => 3,

];