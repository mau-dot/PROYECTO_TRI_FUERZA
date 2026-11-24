@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Estados financieros - Contabilidad')
@section('titulo_modulo', 'Estados financieros')

@section('acciones')
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
@endsection

@section('modulo')

@php
    $f = fn (int $c) => \App\Support\Dinero::formato($c);
@endphp

<ul class="nav nav-pills mb-3 no-print">
    <li class="nav-item"><a class="nav-link {{ $estado === 'resultados' ? 'active' : '' }}" href="{{ route('contabilidad.estados', ['estado' => 'resultados']) }}">Estado de resultados</a></li>
    <li class="nav-item"><a class="nav-link {{ $estado === 'balance' ? 'active' : '' }}" href="{{ route('contabilidad.estados', ['estado' => 'balance']) }}">Balance general</a></li>
</ul>

{{-- Comprobaciones cruzadas --}}
<div class="d-flex flex-wrap gap-2 mb-3 no-print">
    @php
        $coincide = $resultados['utilidad_neta'] === $hoja['utilidad'];
    @endphp
    <span class="badge rounded-pill text-bg-{{ $coincide ? 'success' : 'danger' }}">
        <i class="fa-solid fa-{{ $coincide ? 'check' : 'xmark' }} me-1"></i>Utilidad igual a la hoja de trabajo
    </span>
    <span class="badge rounded-pill text-bg-{{ $balance['cuadra'] ? 'success' : 'danger' }}">
        <i class="fa-solid fa-{{ $balance['cuadra'] ? 'check' : 'xmark' }} me-1"></i>Activo = Pasivo + Patrimonio
    </span>
</div>

@if ($resultados['sin_clasificar']['lineas'])
    <div class="alert alert-warning no-print">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        Hay cuentas de resultado que no calzan con ninguna sección de <code>config/estados_financieros.php</code>:
        {{ collect($resultados['sin_clasificar']['lineas'])->map(fn ($l) => $l['cuenta']->etiqueta())->join(', ') }}.
        Se suman a la utilidad antes de ISR; agrega su prefijo a la sección que corresponda.
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4 mx-auto" style="max-width: 820px;">
    <div class="card-body p-4">
        @if ($estado === 'resultados')
            @include('partials.encabezado-reporte', ['titulo' => 'Estado de resultados', 'subtitulo' => 'Del '.$periodo->rango()])

            @php
                $r = $resultados;
                $s = $r['secciones'];
                // [sección, signo]  o  ['=', subtotal, valor]
                $bloques = [
                    ['ventas', '+'], ['costo', '-'], ['=', 'Utilidad bruta', $r['utilidad_bruta']],
                    ['gastos_operacion', '-'], ['=', 'Utilidad de operación', $r['utilidad_operacion']],
                    ['otros_ingresos', '+'], ['otros_gastos', '-'],
                ];
            @endphp
            <table class="table table-sm table-borderless mb-0 reporte">
                @foreach ($bloques as $b)
                    @if ($b[0] === '=')
                        <tr class="fw-bold border-top"><td>{{ $b[1] }}</td><td></td><td class="num">{{ $f($b[2]) }}</td></tr>
                    @elseif (isset($s[$b[0]]))
                        <tr class="fw-semibold"><td>{{ $b[1] === '-' ? '(-) ' : '' }}{{ $s[$b[0]]['titulo'] }}</td><td></td><td class="num">{{ $f($s[$b[0]]['total']) }}</td></tr>
                        @foreach ($s[$b[0]]['lineas'] as $l)
                            <tr class="text-body-secondary"><td class="ps-4">{{ $l['cuenta']->etiqueta() }}</td><td class="num">{{ $f($l['monto']) }}</td><td></td></tr>
                        @endforeach
                    @endif
                @endforeach
                @if ($r['sin_clasificar']['lineas'])
                    <tr class="fw-semibold text-warning-emphasis"><td>(±) Sin clasificar</td><td></td><td class="num">{{ $f($r['sin_clasificar']['total']) }}</td></tr>
                @endif
                <tr class="fw-bold border-top"><td>Utilidad antes de impuesto sobre la renta</td><td></td><td class="num">{{ $f($r['utilidad_antes_isr']) }}</td></tr>
                @isset($s['isr'])
                    <tr class="fw-semibold"><td>(-) {{ $s['isr']['titulo'] }}</td><td></td><td class="num">{{ $f($s['isr']['total']) }}</td></tr>
                @endisset
                <tr class="fw-bold border-top border-bottom fs-6">
                    <td>{{ $r['utilidad_neta'] >= 0 ? 'UTILIDAD NETA DEL EJERCICIO' : 'PÉRDIDA NETA DEL EJERCICIO' }}</td><td></td>
                    <td class="num">$ {{ $f($r['utilidad_neta']) }}</td>
                </tr>
            </table>
        @else
            @include('partials.encabezado-reporte', ['titulo' => 'Balance general', 'subtitulo' => 'Al '.$periodo->fecha_fin->format('d/m/Y')])

            <table class="table table-sm table-borderless mb-0 reporte">
                @foreach ($balance['grupos'] as $clave => $g)
                    <tr class="fw-bold border-top"><td colspan="2">{{ $g['titulo'] }}</td><td></td></tr>
                    @foreach ($g['rubros'] as $rubro)
                        @if ($rubro['titulo'])
                            <tr class="fw-semibold"><td class="ps-3">{{ $g['titulo'] === 'ACTIVO' ? 'Activo' : 'Pasivo' }} {{ mb_strtolower($rubro['titulo']) }}</td><td class="num">{{ $f($rubro['total']) }}</td><td></td></tr>
                        @endif
                        @foreach ($rubro['lineas'] as $l)
                            <tr class="text-body-secondary">
                                <td class="ps-5">{{ $l['texto'] }}</td>
                                <td class="num">{{ $f($l['monto']) }}</td><td></td>
                            </tr>
                        @endforeach
                    @endforeach
                    <tr class="fw-bold"><td colspan="2">TOTAL {{ $g['titulo'] }}</td><td class="num">{{ $f($g['total']) }}</td></tr>
                    @if ($clave === 'activo')
                        <tr><td colspan="3" class="py-2"></td></tr>
                    @endif
                @endforeach
                <tr class="fw-bold border-top border-bottom fs-6">
                    <td colspan="2">TOTAL PASIVO + PATRIMONIO</td>
                    <td class="num">$ {{ $f($balance['pasivo_mas_patrimonio']) }}</td>
                </tr>
            </table>
        @endif
    </div>
</div>

@endsection