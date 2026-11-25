@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', $costeo->nombre.' - Sistema de costos')
@section('titulo_modulo', 'Costeo de alternativa')

@section('acciones')
    <a href="{{ route('contabilidad.costeos.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Comparativo</a>
    <a href="{{ route('contabilidad.costeos.edit', $costeo) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen me-1"></i>Editar</a>
@endsection

@section('modulo')

@php
    $d = fn (int $c) => \App\Support\Dinero::formato($c);
    $grupos = $costeo->elementos->groupBy(fn ($e) => $e->elemento->value);
    $tarjetas = [
        ['Costo total anual', $resumen['total'], 'fa-coins'],
        ['Costos fijos', $resumen['fijo'], 'fa-lock'],
        ['Costos variables', $resumen['variable'], 'fa-chart-line'],
        ['Costo por '.$costeo->unidad, $resumen['unitario'], 'fa-ruler-horizontal'],
    ];
@endphp

<div class="mb-3">
    <h4 class="fw-bold mb-1">
        {{ $costeo->nombre }}
        @if ($costeo->provisional) <span class="badge text-bg-warning align-middle fs-6">Provisional</span> @endif
    </h4>
    <div class="text-body-secondary">{{ $costeo->sistema->label() }} · {{ number_format($costeo->volumen_anual, 2) }} {{ $costeo->unidad }} al año</div>
    @if ($costeo->descripcion) <p class="mt-2 mb-0">{{ $costeo->descripcion }}</p> @endif
</div>

{{-- Resumen --}}
<div class="row g-3 mb-4">
    @foreach ($tarjetas as [$texto, $valor, $icono])
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
                <div class="card-body">
                    <div class="text-body-secondary small mb-1"><i class="fa-solid {{ $icono }} me-1"></i>{{ $texto }}</div>
                    <div class="valor">$ {{ $d($valor) }}</div>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Elementos del costo --}}
<div class="card border-0 shadow-sm rounded-4">
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0 reporte">
            <thead>
                <tr>
                    <th class="ps-3">Concepto</th>
                    <th>Comportamiento</th>
                    <th class="text-end">Cantidad al año</th>
                    <th class="text-end">Costo unitario</th>
                    <th class="text-end">Monto anual</th>
                    <th>Cuenta</th>
                    <th>Fuentes</th>
                    <th class="pe-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach (\App\Enums\ElementoCosto::cases() as $el)
                    @continue(! $grupos->has($el->value))
                    <tr class="table-active">
                        <td colspan="4" class="ps-3 fw-semibold">{{ $el->sigla() }} · {{ $el->label() }}</td>
                        <td class="num fw-semibold">{{ $d($resumen['por_elemento'][$el->value]) }}</td>
                        <td colspan="3"></td>
                    </tr>
                    @foreach ($grupos[$el->value] as $e)
                        <tr>
                            <td class="ps-4 text-wrap" style="min-width: 16rem;">
                                {{ $e->concepto }}
                                @if ($e->estimado) <span class="badge text-bg-light border">Estimado</span> @endif
                            </td>
                            <td><span class="badge text-bg-{{ $e->comportamiento->color() }}">{{ $e->comportamiento->label() }}</span></td>
                            <td class="num">{{ rtrim(rtrim(number_format($e->cantidad, 2), '0'), '.') }} {{ $e->unidad_medida }}</td>
                            <td class="num">{{ $d(\App\Support\Dinero::centavos($e->costo_unitario)) }}</td>
                            <td class="num">{{ $d($e->monto()) }}</td>
                            <td class="small">
                                @if ($e->cuenta)
                                    <a href="{{ route('contabilidad.mayor', ['cuenta' => $e->cuenta_id]) }}" class="text-decoration-none" title="{{ $e->cuenta->etiqueta() }}">{{ $e->cuenta->codigo }}</a>
                                @else
                                    <span class="text-body-secondary">—</span>
                                @endif
                            </td>
                            <td>@include('partials.fuentes', ['n' => $e->evidencias_count])</td>
                            <td class="pe-3 text-end text-nowrap">
                                <a href="{{ route('contabilidad.costeos.elementos.edit', $e) }}" class="btn btn-sm btn-outline-secondary" title="Editar y evidencias"><i class="fa-solid fa-pen"></i></a>
                                <form method="POST" action="{{ route('contabilidad.costeos.elementos.destroy', $e) }}" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" title="Eliminar" onclick="return confirm('¿Eliminar este costo?')"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                @endforeach
                @if ($costeo->elementos->isEmpty())
                    <tr><td colspan="8" class="text-center text-body-secondary py-4">Todavía no hay costos. Agrégalos abajo.</td></tr>
                @endif
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="ps-3 text-end">Costo total anual de operación</td>
                    <td class="num">$ {{ $d($resumen['total']) }}</td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<p class="small text-body-secondary mt-2">
    Monto anual = cantidad al año × costo unitario. Costo por unidad = $ {{ $d($resumen['total']) }} ÷ {{ number_format($costeo->volumen_anual, 2) }} = <strong>$ {{ $d($resumen['unitario']) }}</strong>.
    Abre cada costo (<i class="fa-solid fa-pen"></i>) para subir o vincular sus evidencias.
</p>

{{-- Agregar un elemento --}}
<div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3"><i class="fa-solid fa-plus me-2"></i>Agregar un costo</h5>
        <form method="POST" action="{{ route('contabilidad.costeos.elementos.store', $costeo) }}" novalidate>
            @csrf
            @include('contabilidad.costeos.campos-elemento')
            <button type="submit" class="btn btn-primary mt-3"><i class="fa-solid fa-floppy-disk me-1"></i>Agregar</button>
        </form>
    </div>
</div>

@include('partials.evidencias', ['modelo' => $costeo, 'tipo' => 'costeo', 'titulo' => 'Evidencias de la alternativa'])

<form method="POST" action="{{ route('contabilidad.costeos.destroy', $costeo) }}" class="mt-3">
    @csrf
    @method('DELETE')
    <button class="btn btn-outline-danger btn-sm" onclick="return confirm('¿Eliminar el costeo y todos sus costos?')">
        <i class="fa-solid fa-trash me-1"></i>Eliminar costeo
    </button>
</form>

@endsection