@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Flujos de caja - Economía')
@section('titulo_modulo', 'Flujos de caja')

@section('acciones')

    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">

        <i class="fa-solid fa-print me-1"></i>
        Imprimir

    </button>

@endsection

@section('modulo')

@include('economia.reportes.encabezado', [
    'titulo' => 'Flujos de caja antes de impuestos'
])

@forelse ($alternativas as $a)

    <div class="card border-0 shadow-sm rounded-4 mb-3"
         id="alt-{{ $a->id }}">

        <div class="card-body pb-0">

            <h6 class="fw-bold mb-1">

                <a href="{{ route('economia.alternativas.show', $a) }}"
                   class="text-decoration-none">

                    {{ $a->nombre }}

                </a>

            </h6>

            <div class="small text-body-secondary mb-2">

                Costo de operación: {{ $a->origenCosto() }}

                @if ($a->costeo)

                    (<a href="{{ route('contabilidad.costeos.show', $a->costeo) }}">
                        {{ $a->costeo->nombre }}
                    </a>)

                @endif

            </div>

        </div>

        <div class="table-responsive">

            <table class="table table-sm align-middle mb-0 reporte">

                <thead>
                    <tr>
                        <th class="ps-3">Año</th>
                        <th class="text-end">Inversión</th>
                        <th class="text-end">Beneficio</th>
                        <th class="text-end">Costo de operación</th>
                        <th class="text-end">Salvamento</th>
                        <th class="text-end pe-3">Flujo neto</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($flujos[$a->id] as $f)

                        <tr>

                            <td class="ps-3">
                                {{ $f['anio'] }}
                            </td>

                            @foreach (['inversion', 'beneficio', 'costo', 'salvamento'] as $col)

                                <td class="num">

                                    {{ \App\Support\Dinero::celda(
                                        \App\Support\Dinero::centavos($f[$col])
                                    ) }}

                                </td>

                            @endforeach

                            <td class="num pe-3 fw-semibold {{ $f['neto'] < 0 ? 'text-danger' : '' }}">

                                {{ \App\Support\Dinero::formato(
                                    \App\Support\Dinero::centavos($f['neto'])
                                ) }}

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>

    </div>

@empty

    <div class="alert alert-info">

        Todavía no hay alternativas.

        <a href="{{ route('economia.alternativas.index') }}">
            Agrégalas
        </a>.

    </div>

@endforelse

<p class="small text-body-secondary">

    Año 0 = inversión.
    Años 1 a n = beneficio − costo de operación
    (este sale del Sistema de costos).

    En el último año se suma el valor de salvamento.

    Los flujos después de impuestos y los indicadores
    (VPN, TIR, CAUE, B/C) se calculan en la fase 7.

</p>

@endsection
