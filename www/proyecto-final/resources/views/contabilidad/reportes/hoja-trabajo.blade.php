@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Hoja de trabajo - Contabilidad')
@section('titulo_modulo', 'Hoja de trabajo')

@section('acciones')
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
@endsection

@section('modulo')

@php($d = fn (int $c) => \App\Support\Dinero::celda($c))
@php($pares = ['bal' => 'Balanza de comprobación', 'aju' => 'Ajustes', 'adj' => 'Balanza ajustada', 'er' => 'Estado de resultados', 'bg' => 'Balance general'])

@include('partials.encabezado-reporte', ['titulo' => 'Hoja de trabajo'])

{{-- Comprobaciones: cada par de columnas debe cuadrar --}}
<div class="d-flex flex-wrap gap-2 mb-3">
    @foreach ($hoja['comprobaciones'] as $texto => $ok)
        <span class="badge rounded-pill text-bg-{{ $ok ? 'success' : 'danger' }}">
            <i class="fa-solid fa-{{ $ok ? 'check' : 'xmark' }} me-1"></i>{{ $texto }}
        </span>
    @endforeach
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-sm table-bordered align-middle mb-0 reporte hoja-trabajo">
            <thead>
                <tr>
                    <th rowspan="2" class="align-bottom ps-2">Código</th>
                    <th rowspan="2" class="align-bottom">Cuenta</th>
                    @foreach ($pares as $titulo)
                        <th colspan="2" class="text-center">{{ $titulo }}</th>
                    @endforeach
                </tr>
                <tr>
                    @foreach ($pares as $titulo)
                        <th class="text-end">Debe</th>
                        <th class="text-end">Haber</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($hoja['filas'] as $f)
                    <tr>
                        <td class="ps-2 font-monospace">{{ $f['cuenta']->codigo }}</td>
                        <td class="nombre">{{ $f['cuenta']->nombre }}</td>
                        @foreach (\App\Services\Contabilidad\HojaTrabajo::COLUMNAS as $col)
                            <td class="num {{ str_starts_with($col, 'aju') && $f[$col] ? 'text-warning-emphasis' : '' }}">{{ $d($f[$col]) }}</td>
                        @endforeach
                    </tr>
                @empty
                    <tr><td colspan="12" class="text-center text-body-secondary py-4">No hay movimientos en este periodo.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="ps-2 text-end">Sumas</td>
                    @foreach (\App\Services\Contabilidad\HojaTrabajo::COLUMNAS as $col)
                        <td class="num">{{ \App\Support\Dinero::formato($hoja['totales'][$col]) }}</td>
                    @endforeach
                </tr>
                <tr class="fw-normal">
                    <td colspan="2" class="ps-2 text-end">{{ $hoja['utilidad'] >= 0 ? 'Utilidad del ejercicio' : 'Pérdida del ejercicio' }}</td>
                    <td colspan="6"></td>
                    <td class="num">{{ $hoja['utilidad'] >= 0 ? $d($hoja['utilidad']) : '' }}</td>
                    <td class="num">{{ $hoja['utilidad'] < 0 ? $d(-$hoja['utilidad']) : '' }}</td>
                    <td class="num">{{ $hoja['utilidad'] < 0 ? $d(-$hoja['utilidad']) : '' }}</td>
                    <td class="num">{{ $hoja['utilidad'] >= 0 ? $d($hoja['utilidad']) : '' }}</td>
                </tr>
                <tr>
                    <td colspan="2" class="ps-2 text-end">Sumas iguales</td>
                    <td colspan="6"></td>
                    @foreach (['er_d', 'er_h', 'bg_d', 'bg_h'] as $col)
                        <td class="num">{{ \App\Support\Dinero::formato($hoja['finales'][$col]) }}</td>
                    @endforeach
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<p class="small text-body-secondary mt-2 no-print">
    Las columnas de ajustes salen de las partidas de tipo <strong>Ajuste</strong> (menu Asientos de ajuste).
    Las cuentas de costo, gasto e ingreso pasan al estado de resultados; las de activo, pasivo y patrimonio al balance general.
</p>

@endsection