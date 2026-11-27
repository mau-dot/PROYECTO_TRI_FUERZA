<?php

/*
|--------------------------------------------------------------------------
| Menu lateral (sidebar) de cada modulo
|--------------------------------------------------------------------------
| Cada opcion tiene:
|   texto  -> lo que se lee en el menú
|   icono  -> clase de Font Awesome 6
|   ruta   -> nombre de la ruta (route name)
|   activa -> patrón de rutas que dejan la opción resaltada (opcional)
|
| Si la ruta TODAVIA NO EXISTE, la opcion aparece gris con "Pronto".
| Asi el menu ya esta completo, de ahi se solo
| agrega sus rutas en routes/web.php: la opcion se activa sola.
*/

return [

    'contabilidad' => [
        'titulo' => 'Contabilidad',
        'icono' => 'fa-solid fa-book',
        'color' => 'primary',
        'inicio' => 'contabilidad.dashboard',
        'secciones' => [
            'General' => [
                ['texto' => 'Dashboard', 'icono' => 'fa-solid fa-gauge', 'ruta' => 'contabilidad.dashboard'],
                ['texto' => 'Empresa', 'icono' => 'fa-solid fa-store', 'ruta' => 'contabilidad.empresa.edit', 'activa' => 'contabilidad.empresa.*'],
                ['texto' => 'Periodos', 'icono' => 'fa-solid fa-calendar-days', 'ruta' => 'contabilidad.periodos.index', 'activa' => 'contabilidad.periodos.*'],
            ],
            'Registro' => [
                ['texto' => 'Catálogo de cuentas', 'icono' => 'fa-solid fa-sitemap', 'ruta' => 'contabilidad.cuentas.index', 'activa' => 'contabilidad.cuentas.*'],
                ['texto' => 'Libro diario', 'icono' => 'fa-solid fa-book-open', 'ruta' => 'contabilidad.partidas.index', 'activa' => 'contabilidad.partidas.*'],
                ['texto' => 'Asientos de ajuste', 'icono' => 'fa-solid fa-sliders', 'ruta' => 'contabilidad.ajustes.index', 'activa' => 'contabilidad.ajustes.*'],
            ],
            'Reportes' => [
                ['texto' => 'Libro mayor / Cuenta T', 'icono' => 'fa-solid fa-table-columns', 'ruta' => 'contabilidad.mayor', 'activa' => 'contabilidad.mayor*'],
                ['texto' => 'Balanza de comprobación', 'icono' => 'fa-solid fa-scale-balanced', 'ruta' => 'contabilidad.balanza'],
                ['texto' => 'Hoja de trabajo', 'icono' => 'fa-solid fa-table', 'ruta' => 'contabilidad.hoja-trabajo'],
                ['texto' => 'Estados financieros', 'icono' => 'fa-solid fa-file-invoice-dollar', 'ruta' => 'contabilidad.estados', 'activa' => 'contabilidad.estados*'],
            ],
            'Costos y respaldo' => [
                ['texto' => 'Sistema de costos', 'icono' => 'fa-solid fa-coins', 'ruta' => 'contabilidad.costeos.index', 'activa' => 'contabilidad.costeos.*'],
                ['texto' => 'Evidencias', 'icono' => 'fa-solid fa-paperclip', 'ruta' => 'evidencias.index', 'activa' => 'evidencias.*'],
            ],
        ],
    ],

    'economia' => [
        'titulo' => 'Economía',
        'icono' => 'fa-solid fa-chart-line',
        'color' => 'success',
        'inicio' => 'economia.dashboard',
        'secciones' => [
            'General' => [
                ['texto' => 'Dashboard', 'icono' => 'fa-solid fa-gauge', 'ruta' => 'economia.dashboard'],
                ['texto' => 'Decisión de inversión', 'icono' => 'fa-solid fa-signs-post', 'ruta' => 'economia.decision.edit', 'activa' => 'economia.decision.*'],
            ],
            'Datos' => [
                ['texto' => 'Alternativas', 'icono' => 'fa-solid fa-code-compare', 'ruta' => 'economia.alternativas.index', 'activa' => 'economia.alternativas.*'],
                ['texto' => 'Flujos de caja', 'icono' => 'fa-solid fa-money-bill-transfer', 'ruta' => 'economia.flujos.index', 'activa' => 'economia.flujos.*'],
            ],
            'Cálculos' => [
                ['texto' => 'Factores y equivalencias', 'icono' => 'fa-solid fa-percent', 'ruta' => 'economia.factores'],
                ['texto' => 'Depreciación', 'icono' => 'fa-solid fa-arrow-trend-down', 'ruta' => 'economia.depreciacion'],
                [ 'texto' => 'VPN · TIR · CAUE · B/C', 'icono' => 'fa-solid fa-calculator', 'ruta' => 'economia.indicadores', ],
                [ 'texto' => 'Después de impuestos', 'icono' => 'fa-solid fa-receipt', 'ruta' => 'economia.impuestos', ],
                [ 'texto' => 'Vida económica', 'icono' => 'fa-solid fa-clock', 'ruta' => 'economia.vida-economica', ],
                [ 'texto' => 'Retiro y reemplazo', 'icono' => 'fa-solid fa-rotate', 'ruta' => 'economia.reemplazo', ],

            ],
            'Decisión' => [
                ['texto' => 'Comparación de alternativas', 'icono' => 'fa-solid fa-chart-column', 'ruta' => 'economia.comparacion', ],
                ['texto' => 'Verificación manual', 'icono' => 'fa-solid fa-list-check', 'ruta' => 'economia.verificacion', 'activa' => 'economia.verificacion*'],
                ['texto' => 'Informe técnico', 'icono' => 'fa-solid fa-print', 'ruta' => 'economia.informe'],
                ['texto' => 'Evidencias', 'icono' => 'fa-solid fa-paperclip', 'ruta' => 'evidencias.index', 'activa' => 'evidencias.*'],
            ],
        ],
    ],

];