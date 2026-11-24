@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Balanza de comprobación - Contabilidad')
@section('titulo_modulo', 'Balanza de comprobación')

@section('acciones')
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
@endsection

@section('modulo')

<div class="d-flex flex-wrap gap-2 mb-3 no-print">
    <div class="btn-group btn-group-sm" role="group" aria-label="Momento">
        <a href="{{ route('contabilidad.balanza', ['nivel' => $nivel]) }}" class="btn btn-outline-secondary {{ $ajustada ? '' : 'active' }}">Antes de ajustes</a>
        <a href="{{ route('contabilidad.balanza', ['ajustada' => 1, 'nivel' => $nivel]) }}" class="btn btn-outline-secondary {{ $ajustada ? 'active' : '' }}">Ajustada</a>
    </div>
    <div class="btn-group btn-group-sm" role="group" aria-label="Nivel">
        <a href="{{ route('contabilidad.balanza', ['ajustada' => (int) $ajustada, 'nivel' => 'detalle']) }}" class="btn btn-outline-secondary {{ $nivel === 'detalle' ? 'active' : '' }}">Cuentas de detalle</a>
        <a href="{{ route('contabilidad.balanza', ['ajustada' => (int) $ajustada, 'nivel' => 'mayor']) }}" class="btn btn-outline-secondary {{ $nivel === 'mayor' ? 'active' : '' }}">Cuentas de mayor</a>
    </div>
</div>

@include('partials.encabezado-reporte', ['titulo' => 'Balanza de comprobación', 'subtitulo' => $ajustada ? 'Ajustada (incluye asientos de ajuste)' : 'Antes de ajustes'])

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-sm table-hover align-middle mb-0 reporte">
            <thead>
                <tr>
                    <th rowspan="2" class="ps-3 align-bottom">Código</th>
                    <th rowspan="2" class="align-bottom">Cuenta</th>
                    <th colspan="2" class="text-center border-bottom-0">Movimientos</th>
                    <th colspan="2" class="text-center border-bottom-0 pe-3">Saldos</th>
                </tr>
                <tr>
                    <th class="text-end">Debe</th>
                    <th class="text-end">Haber</th>
                    <th class="text-end">Deudor</th>
                    <th class="text-end pe-3">Acreedor</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($balanza['filas'] as $f)
                    <tr>
                        <td class="ps-3 font-monospace">{{ $f['cuenta']->codigo }}</td>
                        <td><a href="{{ route('contabilidad.mayor', ['cuenta' => $f['cuenta']->id]) }}" class="text-decoration-none text-body">{{ $f['cuenta']->nombre }}</a></td>
                        <td class="num">{{ \App\Support\Dinero::celda($f['debe']) }}</td>
                        <td class="num">{{ \App\Support\Dinero::celda($f['haber']) }}</td>
                        <td class="num">{{ \App\Support\Dinero::celda($f['deudor']) }}</td>
                        <td class="num pe-3">{{ \App\Support\Dinero::celda($f['acreedor']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">No hay movimientos en este periodo.</td></tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="ps-3 text-end">
                        Totales
                        @if ($balanza['cuadra'])
                            <span class="badge text-bg-success ms-1"><i class="fa-solid fa-check"></i> Cuadra</span>
                        @else
                            <span class="badge text-bg-danger ms-1">No cuadra</span>
                        @endif
                    </td>
                    <td class="num">$ {{ \App\Support\Dinero::formato($balanza['totales']['debe']) }}</td>
                    <td class="num">$ {{ \App\Support\Dinero::formato($balanza['totales']['haber']) }}</td>
                    <td class="num">$ {{ \App\Support\Dinero::formato($balanza['totales']['deudor']) }}</td>
                    <td class="num pe-3">$ {{ \App\Support\Dinero::formato($balanza['totales']['acreedor']) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

@endsection