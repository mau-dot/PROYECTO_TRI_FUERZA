@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', ($tipo?->value === 'ajuste' ? 'Asientos de ajuste' : 'Libro diario').' - Contabilidad')
@section('titulo_modulo', $tipo?->value === 'ajuste' ? 'Asientos de ajuste' : 'Libro diario')

@section('acciones')
    @if ($periodo?->estaAbierto())
        <a href="{{ route('contabilidad.partidas.create', $tipo ? ['tipo' => $tipo->value] : []) }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i>{{ $tipo?->value === 'ajuste' ? 'Nuevo ajuste' : 'Nueva partida' }}
        </a>
    @endif
@endsection

@section('modulo')

@if (! $periodo)
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>No hay periodo contable.
        <a href="{{ route('contabilidad.periodos.index') }}">Crea o activa uno</a> para registrar partidas.
    </div>
@else
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <span class="text-body-secondary small me-2">
            Periodo <strong>{{ $periodo->nombre }}</strong> ({{ $periodo->rango() }})
            @unless ($periodo->estaAbierto()) · <span class="badge text-bg-secondary">Cerrado</span> @endunless
        </span>
        <div class="btn-group btn-group-sm ms-auto" role="group" aria-label="Filtrar por tipo">
            <a href="{{ route('contabilidad.partidas.index') }}" class="btn btn-outline-secondary {{ $tipo ? '' : 'active' }}">Todas</a>
            @foreach ($tipos as $t)
                <a href="{{ route('contabilidad.partidas.index', ['tipo' => $t->value]) }}"
                   class="btn btn-outline-secondary {{ $tipo === $t ? 'active' : '' }}">{{ $t->label() }}</a>
            @endforeach
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 7rem;">Fecha</th>
                        <th style="width: 7rem;">Código</th>
                        <th>Cuenta y concepto</th>
                        <th class="text-end" style="width: 9rem;">Debe</th>
                        <th class="text-end" style="width: 9rem;">Haber</th>
                        <th class="text-end pe-3" style="width: 8rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($partidas as $p)
                        {{-- Encabezado de la partida --}}
                        <tr class="table-active">
                            <td class="ps-3">{{ $p->fecha->format('d/m/Y') }}</td>
                            <td colspan="4">
                                <a href="{{ route('contabilidad.partidas.show', $p) }}" class="fw-bold text-decoration-none">Partida #{{ $p->numero }}</a>
                                <span class="badge text-bg-{{ $p->tipo->color() }} ms-1">{{ $p->tipo->label() }}</span>
                                @include('partials.fuentes', ['n' => $p->evidencias_count])
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                <a href="{{ route('contabilidad.partidas.show', $p) }}" class="btn btn-sm btn-outline-secondary" title="Ver"><i class="fa-solid fa-eye"></i></a>
                                @if ($periodo->estaAbierto())
                                    <a href="{{ route('contabilidad.partidas.edit', $p) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                @endif
                            </td>
                        </tr>
                        {{-- Líneas: primero el debe, y el haber con sangría, como en el libro diario --}}
                        @foreach ($p->detalles->sortByDesc(fn ($d) => (float) $d->debe > 0) as $d)
                            <tr>
                                <td></td>
                                <td class="font-monospace small">{{ $d->cuenta->codigo }}</td>
                                <td class="{{ (float) $d->haber > 0 ? 'ps-5' : '' }}">
                                    {{ $d->cuenta->nombre }}
                                    @if ($d->concepto) <div class="small text-body-secondary">{{ $d->concepto }}</div> @endif
                                </td>
                                <td class="text-end font-monospace">{{ (float) $d->debe > 0 ? number_format($d->debe, 2) : '' }}</td>
                                <td class="text-end font-monospace">{{ (float) $d->haber > 0 ? number_format($d->haber, 2) : '' }}</td>
                                <td></td>
                            </tr>
                        @endforeach
                        <tr>
                            <td></td>
                            <td></td>
                            <td class="small fst-italic text-body-secondary pb-3">v/ {{ $p->concepto }}</td>
                            <td class="text-end font-monospace small border-top pb-3">{{ number_format($p->totalDebe(), 2) }}</td>
                            <td class="text-end font-monospace small border-top pb-3">{{ number_format($p->totalHaber(), 2) }}</td>
                            <td></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">Todavía no hay partidas{{ $tipo ? ' de tipo '.mb_strtolower($tipo->label()) : '' }} en este periodo.</td></tr>
                    @endforelse
                </tbody>
                @if ($partidas->isNotEmpty())
                    <tfoot>
                        <tr class="fw-bold">
                            <td colspan="3" class="ps-3 text-end">Totales ({{ $partidas->count() }} partidas)</td>
                            <td class="text-end font-monospace">$ {{ number_format($totalDebe, 2) }}</td>
                            <td class="text-end font-monospace">$ {{ number_format($totalHaber, 2) }}</td>
                            <td class="pe-3">
                                @if (round($totalDebe, 2) === round($totalHaber, 2))
                                    <span class="badge text-bg-success"><i class="fa-solid fa-check"></i> Cuadra</span>
                                @else
                                    <span class="badge text-bg-danger">No cuadra</span>
                                @endif
                            </td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>
@endif

@endsection