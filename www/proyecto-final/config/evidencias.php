<?php

/*
|--------------------------------------------------------------------------
| Evidencias y trazabilidad
|--------------------------------------------------------------------------
| minimo_fuentes: se pide al menos 2 fuentes por dato clave.
|
| vinculables: a que datos se les puede enlazar una evidencia.
|   La clave (partida, empresa...) es el nombre corto que se guarda en la base
|   (esta registrado en AppServiceProvider con Relation::morphMap).
|   "campos" son los datos concretos de ese registro que se pueden respaldar
|   ('' = el registro completo). Las fases de economia agregaran aqui, ejm , 'alternativa' con costo_inicial, vida_util, valor_salvamento...
*/

return [

    'minimo_fuentes' => 2,

    'tamano_maximo_kb' => 10240, // 10 MB

    'extensiones' => ['pdf', 'jpg', 'jpeg', 'png', 'webp', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt'],

    'vinculables' => [
        'partida' => [
            'nombre' => 'Partida',
            'campos' => ['' => 'Toda la partida'],
        ],
        'empresa' => [
            'nombre' => 'Empresa',
            'campos' => [
                '' => 'Datos de la empresa',
                'autorizacion' => 'Carta de autorización y confidencialidad',
                'estados_financieros' => 'Estados financieros o registros existentes',
            ],
        ],
        'cuenta' => [
            'nombre' => 'Cuenta',
            'campos' => ['' => 'Saldo de la cuenta'],
        ],
         // -> sistema de costos
        'costeo' => [
            'nombre' => 'Costeo',
            'campos' => ['' => 'Datos de la alternativa'],
        ],
        'costeo_elemento' => [
            'nombre' => 'Costo de una alternativa',
            'campos' => [
                '' => 'Todo el costo',
                'cantidad' => 'Cantidad al año',
                'costo_unitario' => 'Costo unitario',
            ],
        ],
    ],

];