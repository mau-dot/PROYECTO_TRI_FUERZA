@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Indicadores económicos - Economía')
@section('titulo_modulo', 'VPN · TIR · CAUE · B/C')

@section('acciones')
    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">
        <i class="fa-solid fa-print me-1"></i>
        Imprimir
    </button>
@endsection

@section('modulo')

    <div class="text-center mb-4">

        <h4 class="fw-bold">
            {{ $empresa?->nombre ?? 'Empresa sin registrar' }}
        </h4>

        <h5>
            Evaluación económica antes de impuestos
        </h5>

        <div class="text-body-secondary">

            {{ $decision->titulo }}

            · TMAR {{ number_format($decision->i() * 100, 2) }} %

            · Cifras en dólares (US$)

            @if ($decision->provisional)
                <span class="badge text-bg-warning">
                    Datos provisionales
                </span>
            @endif

        </div>

    </div>

    @if ($decision->provisional)

        <div class="alert alert-warning">

            <i class="fa-solid fa-triangle-exclamation me-2"></i>

            Los resultados se obtienen utilizando datos
            provisionales. Deben sustituirse por información
            real respaldada con evidencias.

        </div>

    @endif

    @if ($resultados->isEmpty())

        <div class="alert alert-info">

            Todavía no hay alternativas registradas.

            <a href="{{ route('economia.alternativas.index') }}"
               class="alert-link">
                Registrar alternativas
            </a>

        </div>

    @else

        {{-- TABLA GENERAL DE INDICADORES --}}

        <div class="card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body">

                <h5 class="fw-bold mb-3">
                    <i class="fa-solid fa-chart-column me-2"></i>
                    Resumen de indicadores económicos
                </h5>

                <div class="table-responsive">

                    <table class="table table-hover align-middle mb-0">

                        <thead>

                            <tr>
                                <th>Alternativa</th>
                                <th class="text-end">VPN</th>
                                <th class="text-end">TIR</th>
                                <th class="text-end">VA neto</th>
                                <th class="text-end">CAUE costos</th>
                                <th class="text-end">B/C</th>
                                <th>Evaluación</th>
                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($resultados as $item)

                                @php
                                    $a = $item['alternativa'];
                                    $r = $item['calculos'];
                                @endphp

                                <tr>

                                    <td>

                                        <a href="{{ route('economia.alternativas.show', $a) }}"
                                           class="fw-semibold text-decoration-none">

                                            {{ $a->nombre }}

                                        </a>

                                        @if ($r['provisional'])

                                            <span class="badge text-bg-warning">
                                                Provisional
                                            </span>

                                        @endif

                                    </td>

                                    <td class="text-end fw-bold font-monospace
                                        {{ $r['vpn'] >= 0 ? 'text-success' : 'text-danger' }}">

                                        {{ number_format($r['vpn'], 2) }}

                                    </td>

                                    <td class="text-end font-monospace">

                                        {{ $r['tir'] === null
                                            ? 'No disponible'
                                            : number_format($r['tir'] * 100, 2).' %' }}

                                    </td>

                                    <td class="text-end font-monospace">

                                        {{ number_format($r['caue'], 2) }}

                                    </td>

                                    <td class="text-end font-monospace">

                                        {{ number_format($r['caue_costos'], 2) }}

                                    </td>

                                    <td class="text-end font-monospace">

                                        {{ $r['bc'] === null
                                            ? 'No disponible'
                                            : number_format($r['bc'], 3) }}

                                    </td>

                                    <td>

                                        @if ($r['aceptable'])

                                            <span class="badge text-bg-success">
                                                Aceptable
                                            </span>

                                        @else

                                            <span class="badge text-bg-danger">
                                                No aceptable
                                            </span>

                                        @endif

                                    </td>

                                </tr>

                            @endforeach

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

        {{-- DESARROLLO INDIVIDUAL POR ALTERNATIVA --}}

        @foreach ($resultados as $item)

            @php
                $a = $item['alternativa'];
                $r = $item['calculos'];
                $f = $r['factores'];
            @endphp

            <div class="card border-0 shadow-sm rounded-4 mb-4">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-3">

                        {{ $a->nombre }}

                        @if ($r['provisional'])
                            <span class="badge text-bg-warning">
                                Provisional
                            </span>
                        @endif

                    </h4>

                    {{-- DATOS DE ENTRADA --}}

                    <div class="row g-3 mb-4">

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Inversión inicial (P)
                                </div>

                                <div class="fs-4 fw-bold">
                                    ${{ number_format($r['inversion'], 2) }}
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Vida útil (n)
                                </div>

                                <div class="fs-4 fw-bold">
                                    {{ $r['vida_util'] }} años
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Valor de salvamento (VS)
                                </div>

                                <div class="fs-4 fw-bold">
                                    ${{ number_format($r['salvamento'], 2) }}
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Beneficio anual
                                </div>

                                <div class="fs-4 fw-bold">
                                    ${{ number_format($r['beneficio_anual'], 2) }}
                                </div>

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Costo de operación
                                </div>

                                <div class="fs-4 fw-bold">
                                    ${{ number_format($r['costo_operacion'], 2) }}
                                </div>

                                <div class="small text-body-secondary">

                                    {{ $r['origen_costo'] }}

                                </div>

                                @if ($a->costeo_id && \Illuminate\Support\Facades\Route::has('contabilidad.costos.show'))

                                    <a href="{{ route('contabilidad.costos.show', $a->costeo_id) }}"
                                       class="small">

                                        Ver sistema de costos

                                    </a>

                                @else

                                    <a href="{{ route('economia.alternativas.show', $a) }}"
                                       class="small">

                                        Consultar origen y evidencias

                                    </a>

                                @endif

                            </div>

                        </div>

                        <div class="col-md-4">

                            <div class="border rounded-3 p-3 h-100">

                                <div class="small text-body-secondary">
                                    Flujo anual neto
                                </div>

                                <div class="fs-4 fw-bold
                                    {{ $r['flujo_anual'] >= 0 ? 'text-success' : 'text-danger' }}">

                                    ${{ number_format($r['flujo_anual'], 2) }}

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- FACTORES UTILIZADOS --}}

                    <h5 class="fw-bold">
                        Factores de interés utilizados
                    </h5>

                    <div class="table-responsive mb-4">

                        <table class="table table-bordered text-center">

                            <thead>

                                <tr>
                                    <th>P/A</th>
                                    <th>P/F</th>
                                    <th>A/P</th>
                                    <th>A/F</th>
                                </tr>

                            </thead>

                            <tbody>

                                <tr class="font-monospace">

                                    <td>{{ number_format($f['pa'], 6) }}</td>
                                    <td>{{ number_format($f['pf'], 6) }}</td>
                                    <td>{{ number_format($f['ap'], 6) }}</td>
                                    <td>{{ number_format($f['af'], 6) }}</td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                    <div class="row g-3">

                        {{-- VPN --}}

                        <div class="col-lg-6">

                            <div class="border rounded-4 p-3 h-100">

                                <h5 class="fw-bold">
                                    Valor Presente Neto
                                </h5>

                                <div class="small text-body-secondary mb-3">
                                    VPN = −P + FE(P/A) + VS(P/F)
                                </div>

                                <div class="font-monospace small mb-3">

                                    VPN = −{{ number_format($r['inversion'], 2) }}

                                    + ({{ number_format($r['flujo_anual'], 2) }})

                                    ({{ number_format($f['pa'], 6) }})

                                    + {{ number_format($r['salvamento'], 2) }}

                                    ({{ number_format($f['pf'], 6) }})

                                </div>

                                <div class="fs-3 fw-bold
                                    {{ $r['vpn'] >= 0 ? 'text-success' : 'text-danger' }}">

                                    ${{ number_format($r['vpn'], 2) }}

                                </div>

                                <hr>

                                <div class="small">

                                    @if ($r['vpn'] >= 0)

                                        La inversión alcanza o supera
                                        el rendimiento mínimo requerido.

                                    @else

                                        La inversión no alcanza
                                        el rendimiento mínimo requerido.

                                    @endif

                                </div>

                            </div>

                        </div>

                        {{-- TIR --}}

                        <div class="col-lg-6">

                            <div class="border rounded-4 p-3 h-100">

                                <h5 class="fw-bold">
                                    Tasa Interna de Rendimiento
                                </h5>

                                <div class="small text-body-secondary mb-3">
                                    0 = Σ FEₜ / (1 + TIR)ᵗ
                                </div>

                                <div class="small mb-3">
                                    Resultado obtenido mediante
                                    el método de bisección.
                                </div>

                                @if ($r['tir'] !== null)

                                    <div class="fs-3 fw-bold
                                        {{ $r['tir'] >= $decision->i() ? 'text-success' : 'text-danger' }}">

                                        {{ number_format($r['tir'] * 100, 2) }} %

                                    </div>

                                    <hr>

                                    <div class="small">

                                        TIR:

                                        {{ number_format($r['tir'] * 100, 2) }} %

                                        · TMAR:

                                        {{ number_format($decision->i() * 100, 2) }} %

                                    </div>

                                @else

                                    <div class="text-body-secondary">

                                        No se dispone de una TIR válida
                                        mediante el método implementado.

                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- VALOR ANUAL NETO --}}

                        <div class="col-lg-6">

                            <div class="border rounded-4 p-3 h-100">

                                <h5 class="fw-bold">
                                    Valor anual equivalente neto
                                </h5>

                                <div class="small text-body-secondary mb-3">
                                    VA = VPN(A/P)
                                </div>

                                <div class="font-monospace small mb-3">

                                    VA = {{ number_format($r['vpn'], 2) }}

                                    × {{ number_format($f['ap'], 6) }}

                                </div>

                                <div class="fs-3 fw-bold
                                    {{ $r['caue'] >= 0 ? 'text-success' : 'text-danger' }}">

                                    ${{ number_format($r['caue'], 2) }}

                                </div>

                                <div class="small text-body-secondary">
                                    Valor equivalente por año
                                </div>

                            </div>

                        </div>

                        {{-- BENEFICIO COSTO --}}

                        <div class="col-lg-6">

                            <div class="border rounded-4 p-3 h-100">

                                <h5 class="fw-bold">
                                    Relación beneficio/costo
                                </h5>

                                <div class="small text-body-secondary mb-3">
                                    B/C = VP de beneficios / VP de costos
                                </div>

                                <div class="font-monospace small mb-3">

                                    B/C = {{ number_format($r['vp_beneficios'], 2) }}

                                    / {{ number_format($r['vp_costos'], 2) }}

                                </div>

                                @if ($r['bc'] !== null)

                                    <div class="fs-3 fw-bold
                                        {{ $r['bc'] >= 1 ? 'text-success' : 'text-danger' }}">

                                        {{ number_format($r['bc'], 3) }}

                                    </div>

                                    <hr>

                                    <div class="small">

                                        @if ($r['bc'] > 1)

                                            Los beneficios superan los costos.

                                        @elseif ($r['bc'] < 1)

                                            Los costos superan los beneficios.

                                        @else

                                            Los beneficios equivalen a los costos.

                                        @endif

                                    </div>

                                @else

                                    <div class="text-body-secondary">
                                        Indicador no disponible.
                                    </div>

                                @endif

                            </div>

                        </div>

                        {{-- CAUE DE COSTOS --}}

                        <div class="col-12">

                            <div class="border rounded-4 p-3">

                                <h5 class="fw-bold">
                                    Costo Anual Uniforme Equivalente
                                </h5>

                                <div class="small text-body-secondary mb-3">
                                    CAUE = P(A/P) − VS(A/F) + CO
                                </div>

                                <div class="font-monospace small mb-3">

                                    CAUE = {{ number_format($r['inversion'], 2) }}

                                    ({{ number_format($f['ap'], 6) }})

                                    − {{ number_format($r['salvamento'], 2) }}

                                    ({{ number_format($f['af'], 6) }})

                                    + {{ number_format($r['costo_operacion'], 2) }}

                                </div>

                                <div class="fs-3 fw-bold">

                                    ${{ number_format($r['caue_costos'], 2) }}

                                </div>

                                <div class="small text-body-secondary">
                                    Costo equivalente por año.
                                </div>

                            </div>

                        </div>

                    </div>

                    <div class="mt-4">

                        <a href="{{ route('economia.alternativas.show', $a) }}"
                           class="btn btn-outline-secondary">

                            <i class="fa-solid fa-paperclip me-1"></i>
                            Consultar datos y evidencias

                        </a>

                    </div>

                </div>

            </div>

        @endforeach

        {{-- CRITERIOS DE EVALUACION --}}

        <div class="card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">
                    Criterios de interpretación
                </h5>

                <p class="mb-2">
                    <strong>VPN ≥ 0:</strong>
                    la alternativa alcanza o supera
                    la rentabilidad requerida.
                </p>

                <p class="mb-2">
                    <strong>TIR ≥ TMAR:</strong>
                    el rendimiento satisface
                    la tasa mínima requerida,
                    cuando existe una TIR válida.
                </p>

                <p class="mb-2">
                    <strong>VA neto ≥ 0:</strong>
                    la alternativa genera un valor
                    anual equivalente no negativo.
                </p>

                <p class="mb-2">
                    <strong>B/C ≥ 1:</strong>
                    los beneficios igualan o superan
                    los costos equivalentes.
                </p>

                <p class="mb-2">
                    <strong>CAUE de costos:</strong>
                    se prefiere el menor costo anual
                    entre alternativas que proporcionan
                    un servicio equivalente.
                </p>

                <hr>

                <div class="small text-body-secondary">

                    Los indicadores individuales no sustituyen
                    el análisis incremental de alternativas
                    mutuamente excluyentes.

                    Los cálculos utilizan los factores sin
                    redondear. Los valores mostrados se redondean
                    únicamente para facilitar su presentación.

                </div>

            </div>

        </div>

    @endif

@endsection
