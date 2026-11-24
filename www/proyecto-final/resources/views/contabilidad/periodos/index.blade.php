@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Periodos - Contabilidad')
@section('titulo_modulo', 'Periodos contables')

@section('acciones')
    @if ($hayEmpresa)
        <a href="{{ route('contabilidad.periodos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Nuevo periodo</a>
    @endif
@endsection

@section('modulo')

@unless ($hayEmpresa)
    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span><i class="fa-solid fa-triangle-exclamation me-2"></i>Antes de crear periodos registra la empresa.</span>
        <a href="{{ route('contabilidad.empresa.edit') }}" class="btn btn-warning btn-sm">Registrar empresa</a>
    </div>
@endunless

<p class="text-body-secondary">
    El <strong>periodo activo</strong> es con el que trabajan el libro diario, los ajustes, la hoja de trabajo y los estados financieros.
    Un periodo <strong>cerrado</strong> ya no se puede modificar.
</p>

<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Desde</th>
                    <th>Hasta</th>
                    <th>Estado</th>
                    <th class="text-end">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($periodos as $p)
                    <tr>
                        <td class="fw-semibold">
                            {{ $p->nombre }}
                            @if ($activo?->is($p))
                                <span class="badge text-bg-primary ms-1">Activo</span>
                            @endif
                        </td>
                        <td>{{ $p->fecha_inicio->format('d/m/Y') }}</td>
                        <td>{{ $p->fecha_fin->format('d/m/Y') }}</td>
                        <td><span class="badge text-bg-{{ $p->estado->color() }}">{{ $p->estado->label() }}</span></td>
                        <td class="text-end text-nowrap">
                            @unless ($activo?->is($p))
                                <form method="POST" action="{{ route('contabilidad.periodos.activar', $p) }}" class="d-inline">
                                    @csrf
                                    <button class="btn btn-sm btn-outline-primary" title="Trabajar con este periodo">
                                        <i class="fa-solid fa-check"></i> Activar
                                    </button>
                                </form>
                            @endunless

                            @if ($p->estaAbierto())
                                <a href="{{ route('contabilidad.periodos.edit', $p) }}" class="btn btn-sm btn-outline-secondary" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            @endif

                            <form method="POST" action="{{ route('contabilidad.periodos.estado', $p) }}" class="d-inline">
                                @csrf
                                @method('PATCH')
                                @if ($p->estaAbierto())
                                    <button class="btn btn-sm btn-outline-warning" title="Cerrar periodo"
                                            onclick="return confirm('¿Cerrar el periodo {{ $p->nombre }}? Ya no se podrá modificar hasta reabrirlo.')">
                                        <i class="fa-solid fa-lock"></i>
                                    </button>
                                @else
                                    <button class="btn btn-sm btn-outline-success" title="Reabrir periodo">
                                        <i class="fa-solid fa-lock-open"></i>
                                    </button>
                                @endif
                            </form>

                            @if ($p->estaAbierto())
                                <form method="POST" action="{{ route('contabilidad.periodos.destroy', $p) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar"
                                            onclick="return confirm('¿Eliminar el periodo {{ $p->nombre }}?')">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-body-secondary py-4">Todavía no hay periodos.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@endsection