@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Catálogo de cuentas - Contabilidad')
@section('titulo_modulo', 'Catálogo de cuentas')

@section('acciones')
    @unless ($catalogoVacio)
        <a href="{{ route('contabilidad.cuentas.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Nueva cuenta</a>
    @endunless
@endsection

@section('modulo')

@if ($catalogoVacio)
    <div class="card border-0 shadow-sm rounded-4" style="max-width: 760px;">
        <div class="card-body p-4">
            <h5 class="fw-bold"><i class="fa-solid fa-sitemap me-2"></i>El catálogo está vacío</h5>
            <p class="text-body-secondary">
                Puedes cargar un <strong>catálogo base para empresa comercial</strong> (88 cuentas, estructura usual en El Salvador:
                1 Activo, 2 Pasivo, 3 Patrimonio, 4 Costos y gastos, 5 Ingresos, 6 Liquidadora) y luego ajustarlo a la tienda,
                o crear las cuentas una por una.
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('contabilidad.cuentas.cargar-base') }}">
                    @csrf
                    <button class="btn btn-primary"><i class="fa-solid fa-file-import me-1"></i>Cargar catálogo base comercial</button>
                </form>
                <a href="{{ route('contabilidad.cuentas.create') }}" class="btn btn-outline-secondary">Crear cuentas a mano</a>
            </div>
        </div>
    </div>
@else
    <form method="GET" class="mb-3" style="max-width: 420px;">
        <div class="input-group">
            <input type="search" name="buscar" value="{{ $buscar }}" class="form-control" placeholder="Buscar por código o nombre">
            <button class="btn btn-outline-secondary"><i class="fa-solid fa-magnifying-glass"></i></button>
        </div>
    </form>

    <p class="small text-body-secondary">
        <span class="badge text-bg-success">Detalle</span> = recibe movimientos en el libro diario (no tiene subcuentas).
        Las cuentas de grupo solo suman a sus subcuentas.
    </p>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3">Código</th>
                        <th>Nombre</th>
                        <th>Tipo</th>
                        <th>Naturaleza</th>
                        <th>Rubro</th>
                        <th></th>
                        <th class="text-end pe-3">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($cuentas as $c)
                        @php($esGrupo = $c->hijas_count > 0)
                        <tr class="{{ $c->activa ? '' : 'opacity-50' }}">
                            <td class="ps-3 font-monospace">{{ $c->codigo }}</td>
                            <td style="padding-left: {{ 0.5 + ($c->nivel - 1) * 1.25 }}rem" class="{{ $esGrupo ? 'fw-bold' : '' }}">
                                {{ $c->nombre }}
                                @unless ($c->activa) <span class="badge text-bg-secondary ms-1">Inactiva</span> @endunless
                            </td>
                            <td>{{ $c->tipo->label() }}</td>
                            <td>{{ $c->naturaleza->label() }}</td>
                            <td>{{ $c->rubro?->label() }}</td>
                            <td class="text-nowrap">
                                @unless ($esGrupo) <span class="badge text-bg-success">Detalle</span> @endunless
                                @if ($c->movimientos_count) <span class="badge text-bg-light" title="Líneas en el libro diario">{{ $c->movimientos_count }} mov.</span> @endif
                            </td>
                            <td class="text-end pe-3 text-nowrap">
                                @unless ($c->movimientos_count)
                                    <a href="{{ route('contabilidad.cuentas.create', ['padre' => $c->codigo]) }}" class="btn btn-sm btn-outline-primary" title="Agregar subcuenta">
                                        <i class="fa-solid fa-plus"></i>
                                    </a>
                                @endunless
                                <a href="{{ route('contabilidad.cuentas.edit', $c) }}" class="btn btn-sm btn-outline-secondary" title="Editar"><i class="fa-solid fa-pen"></i></a>
                                <form method="POST" action="{{ route('contabilidad.cuentas.estado', $c) }}" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    <button class="btn btn-sm btn-outline-warning" title="{{ $c->activa ? 'Desactivar' : 'Activar' }}">
                                        <i class="fa-solid {{ $c->activa ? 'fa-eye-slash' : 'fa-eye' }}"></i>
                                    </button>
                                </form>
                                @if (! $esGrupo && ! $c->movimientos_count)
                                    <form method="POST" action="{{ route('contabilidad.cuentas.destroy', $c) }}" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-outline-danger" title="Eliminar"
                                                onclick="return confirm('¿Eliminar la cuenta {{ $c->codigo }} - {{ $c->nombre }}?')">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-body-secondary py-4">No hay cuentas que coincidan con "{{ $buscar }}".</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endif

@endsection