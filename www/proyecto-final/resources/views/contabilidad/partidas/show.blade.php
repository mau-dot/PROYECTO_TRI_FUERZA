@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', "Partida #{$partida->numero} - Contabilidad")
@section('titulo_modulo', "Partida #{$partida->numero}")

@section('acciones')
    <a href="{{ route('contabilidad.partidas.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Libro diario</a>
    @include('partials.evidencias', ['modelo' => $partida, 'tipo' => 'partida', 'titulo' => 'Evidencias de esta partida'])
    @if ($partida->periodo->estaAbierto())
        <a href="{{ route('contabilidad.partidas.edit', $partida) }}" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i>Editar</a>
    @endif
@endsection

@section('modulo')

<div class="card border-0 shadow-sm rounded-4 mb-3">
    <div class="card-body p-4">
        <div class="row g-3">
            <div class="col-sm-3"><div class="small text-body-secondary">Fecha</div><div class="fw-semibold">{{ $partida->fecha->format('d/m/Y') }}</div></div>
            <div class="col-sm-3"><div class="small text-body-secondary">Tipo</div><span class="badge text-bg-{{ $partida->tipo->color() }}">{{ $partida->tipo->label() }}</span></div>
            <div class="col-sm-3"><div class="small text-body-secondary">Periodo</div><div class="fw-semibold">{{ $partida->periodo->nombre }}</div></div>
            <div class="col-sm-3"><div class="small text-body-secondary">Registrada por</div><div class="fw-semibold">{{ $partida->usuario?->username ?? '—' }}</div></div>
            <div class="col-12"><div class="small text-body-secondary">Concepto</div><div>{{ $partida->concepto }}</div></div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th class="ps-3">Código</th>
                    <th>Cuenta</th>
                    <th>Concepto de la línea</th>
                    <th class="text-end">Debe</th>
                    <th class="text-end pe-3">Haber</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($partida->detalles as $d)
                    <tr>
                        <td class="ps-3 font-monospace">{{ $d->cuenta->codigo }}</td>
                        <td>{{ $d->cuenta->nombre }}</td>
                        <td class="text-body-secondary">{{ $d->concepto }}</td>
                        <td class="text-end font-monospace">{{ (float) $d->debe > 0 ? number_format($d->debe, 2) : '' }}</td>
                        <td class="text-end font-monospace pe-3">{{ (float) $d->haber > 0 ? number_format($d->haber, 2) : '' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="3" class="ps-3 text-end">Totales</td>
                    <td class="text-end font-monospace">$ {{ number_format($partida->totalDebe(), 2) }}</td>
                    <td class="text-end font-monospace pe-3">$ {{ number_format($partida->totalHaber(), 2) }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection