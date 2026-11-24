{{--
    Encabezado de un reporte contable (se ve en pantalla y al imprimir). Uso:
        @include('partials.encabezado-reporte', ['titulo' => 'Balanza de comprobación'])
        opcional: 'subtitulo' => 'Antes de ajustes'
    El boton "Imprimir" va en @section('acciones'):
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">...</button>
--}}
<div class="text-center mb-3 encabezado-reporte">
    <div class="fw-bold fs-5">{{ $empresa?->nombre ?? 'Empresa sin registrar' }}</div>
    <div class="fw-semibold">{{ $titulo }}</div>
    <div class="small text-body-secondary">
        {{ isset($subtitulo) ? $subtitulo.' · ' : '' }}Periodo {{ $periodo->nombre }} ({{ $periodo->rango() }}) · Cifras en dólares (US$)
    </div>
</div>