@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Sistema de costos - Contabilidad')
@section('titulo_modulo', 'Sistema de costos')

@section('acciones')
    @if ($costeos->isNotEmpty())
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
        <a href="{{ route('contabilidad.costeos.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i>Nuevo costeo</a>
    @endif
@endsection

@section('modulo')

@php
    $d = fn (int $c) => \App\Support\Dinero::formato($c);
    $hayProvisionales = $costeos->contains('provisional', true);
@endphp

@if ($costeos->isEmpty())
    <div class="card border-0 shadow-sm rounded-4" style="max-width: 760px;">
        <div class="card-body p-4">
            <h5 class="fw-bold"><i class="fa-solid fa-coins me-2"></i>Todavía no hay costeos</h5>
            <p class="text-body-secondary">
                Se hace <strong>un costeo por cada alternativa de inversión</strong>: cuánto cuesta al año operarla
                (mercadería, mano de obra, costos indirectos y gastos), separado en <strong>fijo</strong> y <strong>variable</strong>.
                El costo total anual pasa después a los flujos de caja del módulo de Economía.
            </p>
            <p class="text-body-secondary small">
                Mientras llega la propuesta real de la tienda, pueden cargar un <strong>ejemplo provisional</strong>
                (renovar la estantería: metálicos nuevos, metálicos usados o de madera a la medida). Todos sus datos quedan
                marcados como <span class="badge text-bg-warning">Provisional</span> y <span class="badge text-bg-light">Estimado</span>.
            </p>
            <div class="d-flex gap-2 flex-wrap">
                <form method="POST" action="{{ route('contabilidad.costeos.cargar-ejemplo') }}">
                    @csrf
                    <button class="btn btn-warning"><i class="fa-solid fa-flask me-1"></i>Cargar ejemplo provisional (estantes)</button>
                </form>
                <a href="{{ route('contabilidad.costeos.create') }}" class="btn btn-outline-secondary">Crear un costeo a mano</a>
            </div>
        </div>
    </div>
@else
    @if ($hayProvisionales)
        <div class="alert alert-warning no-print">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            Hay costeos <strong>provisionales</strong>, con datos supuestos. Reemplácenlos por la propuesta real de la tienda
            y respalden cada costo con sus evidencias.
        </div>
    @endif

    <div class="text-center mb-3">
        <div class="fw-bold fs-5">{{ \App\Models\Empresa::actual()?->nombre ?? 'Empresa sin registrar' }}</div>
        <div class="fw-semibold">Cuadro comparativo de costos por alternativa</div>
        <div class="small text-body-secondary">Costos estándar anuales · Cifras en dólares (US$)</div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-sm align-middle mb-0 reporte">
                <thead>
                    <tr>
                        <th class="ps-3">Concepto</th>
                        @foreach ($costeos as $c)
                            <th class="text-end text-wrap" style="min-width: 11rem;">
                                <a href="{{ route('contabilidad.costeos.show', $c) }}" class="text-decoration-none">{{ $c->nombre }}</a>
                                @if ($c->provisional) <div><span class="badge text-bg-warning">Provisional</span></div> @endif
                            </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="ps-3 text-body-secondary">Unidad de costeo</td>
                        @foreach ($costeos as $c)
                            <td class="text-end small">{{ number_format($c->volumen_anual, 2) }} {{ $c->unidad }}</td>
                        @endforeach
                    </tr>
                    @foreach ($elementos as $el)
                        <tr>
                            <td class="ps-3">{{ $el->label() }} ({{ $el->sigla() }})</td>
                            @foreach ($costeos as $c)
                                <td class="num">{{ \App\Support\Dinero::celda($resumenes[$c->id]['por_elemento'][$el->value]) }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                    <tr class="border-top">
                        <td class="ps-3">Costos fijos</td>
                        @foreach ($costeos as $c)
                            <td class="num">{{ $d($resumenes[$c->id]['fijo']) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="ps-3">Costos variables</td>
                        @foreach ($costeos as $c)
                            <td class="num">{{ $d($resumenes[$c->id]['variable']) }}</td>
                        @endforeach
                    </tr>
                    <tr class="fw-bold border-top">
                        <td class="ps-3">Costo total anual de operación</td>
                        @foreach ($costeos as $c)
                            <td class="num">$ {{ $d($resumenes[$c->id]['total']) }}</td>
                        @endforeach
                    </tr>
                    <tr class="fw-bold">
                        <td class="ps-3">Costo por unidad</td>
                        @foreach ($costeos as $c)
                            <td class="num">
                                @if ($masBarato === $c->id) <span class="badge text-bg-success me-1">Menor</span> @endif
                                $ {{ $d($resumenes[$c->id]['unitario']) }}
                            </td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="ps-3 text-body-secondary">Costo variable por unidad</td>
                        @foreach ($costeos as $c)
                            <td class="num">{{ $d($resumenes[$c->id]['variable_unitario']) }}</td>
                        @endforeach
                    </tr>
                    <tr>
                        <td class="ps-3 text-body-secondary">Datos estimados (sin confirmar)</td>
                        @foreach ($costeos as $c)
                            <td class="text-end small">{{ $resumenes[$c->id]['estimados'] }} de {{ $resumenes[$c->id]['elementos'] }}</td>
                        @endforeach
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <p class="small text-body-secondary mt-2">
        El <strong>costo por unidad</strong> permite comparar alternativas de distinto tamaño. El <strong>costo total anual</strong>
        es el costo de operación que pasa a los flujos de caja (módulo de Economía). La inversión inicial, la vida útil y el valor
        de salvamento se registran en <em>Economía → Alternativas</em>.
    </p>
@endif

@endsection