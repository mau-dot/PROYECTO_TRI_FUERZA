@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Libro mayor - Contabilidad')
@section('titulo_modulo', 'Libro mayor / Cuenta T')

@section('acciones')
    <button type="button" class="btn btn-outline-secondary" onclick="window.print()"><i class="fa-solid fa-print me-1"></i>Imprimir</button>
@endsection

@section('modulo')

{{-- Selector de cuenta: cualquier cuenta; si es de grupo se juntan sus subcuentas --}}
<form method="GET" class="d-flex gap-2 mb-3 no-print">
    <select name="cuenta" class="form-select" onchange="this.form.submit()">
        <option value="">— Todas las cuentas con movimiento —</option>
        @foreach ($cuentas as $c)
            <option value="{{ $c->id }}" @selected($cuenta?->id === $c->id)>{{ str_repeat('· ', $c->nivel - 1) }}{{ $c->etiqueta() }}</option>
        @endforeach
    </select>
    <noscript><button class="btn btn-primary">Ver</button></noscript>
</form>

@if (! $cuenta)
    @include('partials.encabezado-reporte', ['titulo' => 'Libro mayor', 'subtitulo' => 'Resumen de cuentas'])

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table table-sm table-hover align-middle mb-0 reporte">
                <thead>
                    <tr>
                        <th class="ps-3">Código</th>
                        <th>Cuenta</th>
                        <th>Naturaleza</th>
                        <th class="text-end">Debe</th>
                        <th class="text-end">Haber</th>
                        <th class="text-end pe-3">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($resumen as $r)
                        <tr>
                            <td class="ps-3 font-monospace">{{ $r['cuenta']->codigo }}</td>
                            <td><a href="{{ route('contabilidad.mayor', ['cuenta' => $r['cuenta']->id]) }}" class="text-decoration-none">{{ $r['cuenta']->nombre }}</a></td>
                            <td class="small text-body-secondary">{{ $r['cuenta']->naturaleza->label() }}</td>
                            <td class="num">{{ \App\Support\Dinero::celda($r['debe']) }}</td>
                            <td class="num">{{ \App\Support\Dinero::celda($r['haber']) }}</td>
                            <td class="num pe-3 fw-semibold">{{ \App\Support\Dinero::formato($r['saldo']) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-body-secondary py-4">No hay movimientos en este periodo.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <p class="small text-body-secondary mt-2 no-print">Haz clic en una cuenta para ver su mayor y su cuenta T. Un saldo entre paréntesis es contrario a la naturaleza de la cuenta.</p>
@else
    @include('partials.encabezado-reporte', ['titulo' => 'Libro mayor', 'subtitulo' => $cuenta->etiqueta()])

    <div class="row g-3">
        {{-- Mayor en columnas, con saldo acumulado --}}
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0 reporte">
                        <thead>
                            <tr>
                                <th class="ps-3">Fecha</th>
                                <th>Partida</th>
                                <th>Concepto</th>
                                <th class="text-end">Debe</th>
                                <th class="text-end">Haber</th>
                                <th class="text-end pe-3">Saldo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($mayor['movimientos'] as $m)
                                <tr>
                                    <td class="ps-3">{{ $m['fecha']->format('d/m/Y') }}</td>
                                    <td>
                                        <a href="{{ route('contabilidad.partidas.show', $m['partida_id']) }}" class="text-decoration-none">#{{ $m['numero'] }}</a>
                                        <span class="badge text-bg-{{ $m['tipo']->color() }}">{{ $m['tipo']->label() }}</span>
                                    </td>
                                    <td class="text-wrap">
                                        {{ $m['concepto'] }}
                                        @if ($m['cuenta']) <div class="small text-body-secondary">{{ $m['cuenta'] }}</div> @endif
                                    </td>
                                    <td class="num">{{ \App\Support\Dinero::celda($m['debe']) }}</td>
                                    <td class="num">{{ \App\Support\Dinero::celda($m['haber']) }}</td>
                                    <td class="num pe-3">{{ \App\Support\Dinero::formato($m['saldo']) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-body-secondary py-4">La cuenta no tiene movimientos en este periodo.</td></tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="ps-3 text-end">Totales</td>
                                <td class="num">{{ \App\Support\Dinero::formato($mayor['total_debe']) }}</td>
                                <td class="num">{{ \App\Support\Dinero::formato($mayor['total_haber']) }}</td>
                                <td class="num pe-3">{{ \App\Support\Dinero::formato($mayor['saldo']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        {{-- Cuenta T: cargos a la izquierda, abonos a la derecha --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body">
                    <div class="text-center fw-bold mb-1">{{ $cuenta->nombre }}</div>
                    <div class="text-center small text-body-secondary mb-2">Naturaleza {{ mb_strtolower($cuenta->naturaleza->label()) }}</div>
                    <div class="row g-0 cuenta-t font-monospace small">
                        <div class="col-6 pe-2 pt-1">
                            @foreach ($mayor['movimientos'] as $m)
                                @if ($m['debe']) <div class="d-flex"><span class="text-body-secondary">({{ $m['numero'] }})</span><span class="ms-auto">{{ \App\Support\Dinero::formato($m['debe']) }}</span></div> @endif
                            @endforeach
                        </div>
                        <div class="col-6 ps-2 pt-1">
                            @foreach ($mayor['movimientos'] as $m)
                                @if ($m['haber']) <div class="d-flex"><span class="text-body-secondary">({{ $m['numero'] }})</span><span class="ms-auto">{{ \App\Support\Dinero::formato($m['haber']) }}</span></div> @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="row g-0 font-monospace small border-top mt-2 pt-1 fw-bold">
                        <div class="col-6 pe-2 text-end">{{ \App\Support\Dinero::formato($mayor['total_debe']) }}</div>
                        <div class="col-6 ps-2 text-end">{{ \App\Support\Dinero::formato($mayor['total_haber']) }}</div>
                    </div>
                    @php($neto = $mayor['total_debe'] - $mayor['total_haber'])
                    <div class="row g-0 font-monospace small fw-bold">
                        <div class="col-6 pe-2 text-end">{{ $neto > 0 ? 'SD '.\App\Support\Dinero::formato($neto) : '' }}</div>
                        <div class="col-6 ps-2 text-end">{{ $neto < 0 ? 'SA '.\App\Support\Dinero::formato(-$neto) : '' }}</div>
                    </div>
                    <div class="small text-body-secondary mt-2">(n) = número de partida · SD = saldo deudor · SA = saldo acreedor</div>
                </div>
            </div>
        </div>
    </div>

    <div class="no-print">
        @include('partials.evidencias', ['modelo' => $cuenta, 'tipo' => 'cuenta', 'titulo' => 'Evidencias del saldo de la cuenta'])
    </div>
@endif

@endsection