@extends('layouts.modulo', ['modulo' => session('modulo', 'contabilidad')])

@section('title', 'Evidencias')
@section('titulo_modulo', 'Evidencias')

@section('acciones')
    <a href="{{ route('evidencias.create') }}" class="btn btn-primary"><i class="fa-solid fa-upload me-1"></i>Subir evidencia</a>
@endsection

@section('modulo')

<p class="text-body-secondary">
    Facturas, cotizaciones, estados financieros, entrevistas y fotografías que respaldan los datos.
    La guía pide que cada dato clave tenga <strong>al menos {{ $minimo }} fuentes</strong> y que se pueda consultar junto al dato.
</p>

@if ($periodo && $resumen['total'])
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 tarjeta-dato h-100"><div class="card-body">
                <div class="small text-body-secondary">Partidas sin evidencia</div>
                <div class="valor text-danger">{{ $resumen['sin'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 tarjeta-dato h-100"><div class="card-body">
                <div class="small text-body-secondary">Con menos de {{ $minimo }} fuentes</div>
                <div class="valor text-warning">{{ $resumen['incompletas'] }}</div>
            </div></div>
        </div>
        <div class="col-sm-4">
            <div class="card border-0 shadow-sm rounded-4 tarjeta-dato h-100"><div class="card-body">
                <div class="small text-body-secondary">Con {{ $minimo }} o más fuentes</div>
                <div class="valor text-success">{{ $resumen['completas'] }} <span class="fs-6 text-body-secondary">de {{ $resumen['total'] }}</span></div>
            </div></div>
        </div>
    </div>
@endif

<div class="d-flex flex-wrap gap-2 mb-3">
    <form method="GET" class="d-flex gap-2" style="max-width: 520px;">
        <select name="tipo" class="form-select" onchange="this.form.submit()">
            <option value="">Todos los tipos</option>
            @foreach ($tipos as $t)
                <option value="{{ $t->value }}" @selected($tipo === $t)>{{ $t->label() }}</option>
            @endforeach
        </select>
        <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por título o fuente">
        <button class="btn btn-outline-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
    </form>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Evidencia</th>
                    <th>Tipo</th>
                    <th>Fuente</th>
                    <th>Fecha</th>
                    <th class="text-center">Respalda</th>
                    <th class="text-end pe-3">Archivo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($evidencias as $e)
                    <tr>
                        <td class="ps-3">
                            <i class="{{ $e->tipo->icono() }} fa-fw text-body-secondary me-1"></i>
                            <a href="{{ route('evidencias.show', $e) }}" class="fw-semibold text-decoration-none">{{ $e->titulo }}</a>
                        </td>
                        <td>{{ $e->tipo->label() }}</td>
                        <td>{{ $e->fuente }}@if ($e->cargo) <span class="small text-body-secondary">({{ $e->cargo }})</span>@endif</td>
                        <td>{{ $e->fecha?->format('d/m/Y') }}</td>
                        <td class="text-center">
                            @if ($e->vinculos_count)
                                <span class="badge text-bg-light">{{ $e->vinculos_count }} {{ $e->vinculos_count === 1 ? 'dato' : 'datos' }}</span>
                            @else
                                <span class="badge text-bg-secondary" title="No está vinculada a ningún dato">Sin vincular</span>
                            @endif
                        </td>
                        <td class="text-end pe-3">
                            @if ($e->archivo_ruta)
                                <a href="{{ route('evidencias.archivo', $e) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="{{ $e->archivo_nombre }}">
                                    <i class="fa-solid fa-up-right-from-square"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-body-secondary py-4">Todavía no hay evidencias{{ $tipo || $buscar ? ' que coincidan' : '' }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection