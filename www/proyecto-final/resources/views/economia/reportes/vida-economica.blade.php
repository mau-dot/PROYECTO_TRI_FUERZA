@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Vida económica - Economía')
@section('titulo_modulo', 'Vida económica')

@section('acciones')
    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">
        <i class="fa-solid fa-print me-1"></i>
        Imprimir
    </button>
@endsection

@section('modulo')

    {{-- ENCABEZADO --}}

    <div class="text-center mb-4">

        <h4 class="fw-bold">
            {{ $empresa?->nombre ?? 'Empresa sin registrar' }}
        </h4>

        <h5>
            Determinación de la vida económica del activo
        </h5>

        <div class="text-body-secondary">

            {{ $decision->titulo }}

            · TMAR {{ number_format($decision->i() * 100, 2) }} %

            · Cifras en dólares (US$)

            <span class="badge text-bg-warning">
                Proyección provisional
            </span>

        </div>

    </div>

    <div class="alert alert-info">

        <i class="fa-solid fa-circle-info me-2"></i>

        La vida económica corresponde al número de años
        durante los cuales resulta conveniente conservar
        un activo, minimizando su costo anual uniforme
        equivalente (CAUE).

    </div>

    <div class="alert alert-warning">

        <i class="fa-solid fa-triangle-exclamation me-2"></i>

        Los valores futuros de mercado y los costos de
        operación se estiman mediante tasas de proyección.
        Estos supuestos deberán respaldarse con información
        real para la evaluación definitiva.

    </div>

    {{-- FORMULARIO DE PARAMETROS --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                <i class="fa-solid fa-sliders me-2"></i>
                Parámetros de evaluación

            </h5>

            @if ($errors->any())

                <div class="alert alert-danger">

                    <ul class="mb-0">

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>

            @endif

            <form method="GET"
                  action="{{ route('economia.vida-economica') }}">

                <div class="row g-3">

                    <div class="col-12">

                        <label for="alternativa"
                               class="form-label">

                            Alternativa de inversión

                        </label>

                        <select id="alternativa"
                                name="alternativa"
                                class="form-select"
                                required>

                            @foreach ($alternativas as $a)

                                <option value="{{ $a->id }}"
                                    @selected($alternativa->id === $a->id)>

                                    {{ $a->nombre }}

                                </option>

                            @endforeach

                        </select>

                    </div>

                    <div class="col-md-6">

                        <label for="perdida_valor"
                               class="form-label">

                            Pérdida anual del valor de mercado (%)

                        </label>

                        <div class="input-group">

                            <input type="number"
                                   id="perdida_valor"
                                   name="perdida_valor"
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   value="{{ $perdidaValor }}"
                                   required>

                            <span class="input-group-text">
                                %
                            </span>

                        </div>

                        <div class="form-text">

                            Porcentaje de disminución anual
                            del valor comercial del activo.

                        </div>

                    </div>

                    <div class="col-md-6">

                        <label for="crecimiento_costo"
                               class="form-label">

                            Incremento anual del costo de operación (%)

                        </label>

                        <div class="input-group">

                            <input type="number"
                                   id="crecimiento_costo"
                                   name="crecimiento_costo"
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.01"
                                   value="{{ $crecimientoCosto }}"
                                   required>

                            <span class="input-group-text">
                                %
                            </span>

                        </div>

                        <div class="form-text">

                            Incremento estimado de los costos
                            de operación y mantenimiento.

                        </div>

                    </div>

                    <div class="col-12">

                        <button type="submit"
                                class="btn btn-success">

                            <i class="fa-solid fa-calculator me-1"></i>
                            Calcular vida económica

                        </button>

                        <a href="{{ route('economia.vida-economica') }}"
                           class="btn btn-outline-secondary">

                            Restablecer

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- DATOS DE LA ALTERNATIVA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                {{ $alternativa->nombre }}

            </h5>

            <div class="row g-3">

                @foreach ([
                    ['Inversión inicial', '$'.number_format($resultado['inversion'], 2)],
                    ['Costo inicial de operación', '$'.number_format($resultado['costo_inicial'], 2)],
                    ['Vida útil', $resultado['vida_util'].' años'],
                    ['TMAR', number_format($resultado['tmar'] * 100, 2).' %'],
                    ['Pérdida anual de valor', number_format($perdidaValor, 2).' %'],
                    ['Crecimiento anual del costo', number_format($crecimientoCosto, 2).' %']
                ] as $dato)

                    <div class="col-md-4">

                        <div class="border rounded-3 p-3 h-100">

                            <div class="small text-body-secondary">

                                {{ $dato[0] }}

                            </div>

                            <div class="fs-4 fw-bold">

                                {{ $dato[1] }}

                            </div>

                        </div>

                    </div>

                @endforeach

            </div>

            <div class="mt-3 text-body-secondary">

                Origen del costo de operación:

                <strong>
                    {{ $resultado['origen_costo'] }}
                </strong>

            </div>

            <div class="mt-3">

                <a href="{{ route('economia.alternativas.show', $alternativa) }}"
                   class="btn btn-outline-secondary btn-sm">

                    <i class="fa-solid fa-paperclip me-1"></i>
                    Consultar datos y evidencias

                </a>

            </div>

        </div>

    </div>

    {{-- RESULTADOS PRINCIPALES --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        Vida económica óptima

                    </div>

                    <div class="fs-2 fw-bold text-success">

                        {{ $resultado['vida_economica'] }}
                        año(s)

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        CAUE mínimo

                    </div>

                    <div class="fs-2 fw-bold text-success">

                        ${{ number_format($resultado['caue_minimo'], 2) }}

                    </div>

                    <div class="small text-body-secondary">

                        Costo equivalente anual

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        Valor de mercado óptimo

                    </div>

                    <div class="fs-3 fw-bold">

                        ${{ number_format($resultado['valor_mercado_optimo'], 2) }}

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        Costo de operación en el año óptimo

                    </div>

                    <div class="fs-3 fw-bold">

                        ${{ number_format($resultado['costo_operacion_optimo'], 2) }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- TABLA DE VIDA ECONOMICA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Cuadro de determinación de la vida económica

            </h5>

            <div class="table-responsive">

                <table class="table table-bordered table-hover
                              align-middle text-end mb-0">

                    <thead>

                        <tr>

                            <th class="text-start">Año</th>
                            <th>Valor de mercado</th>
                            <th>Costo de operación</th>
                            <th>VP del mercado</th>
                            <th>VP costos acumulados</th>
                            <th>CAUE capital</th>
                            <th>CAUE operación</th>
                            <th>CAUE total</th>
                            <th class="text-center">Evaluación</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($resultado['filas'] as $fila)

                            <tr class="font-monospace
                                {{ $fila['optimo'] ? 'table-success fw-bold' : '' }}">

                                <td class="text-start">

                                    {{ $fila['anio'] }}

                                </td>

                                @foreach ([
                                    'valor_mercado',
                                    'costo_operacion',
                                    'vp_mercado',
                                    'vp_costos_acumulados',
                                    'caue_capital',
                                    'caue_operacion',
                                    'caue_total'
                                ] as $campo)

                                    <td>

                                        {{ number_format($fila[$campo], 2) }}

                                    </td>

                                @endforeach

                                <td class="text-center">

                                    @if ($fila['optimo'])

                                        <span class="badge text-bg-success">

                                            <i class="fa-solid fa-check me-1"></i>
                                            Óptimo

                                        </span>

                                    @else

                                        —

                                    @endif

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="small text-body-secondary mt-3">

                La fila resaltada corresponde al año
                con el menor costo anual equivalente.

            </div>

        </div>

    </div>

    {{-- GRAFICO SVG DEL CAUE --}}

    @php

        $filas = $resultado['filas'];

        $cantidad = count($filas);

        $minimo = min(array_column($filas, 'caue_total'));

        $maximo = max(array_column($filas, 'caue_total'));

        $margen = max(($maximo - $minimo) * 0.15, 1);

        $limiteInferior = $minimo - $margen;

        $limiteSuperior = $maximo + $margen;

        $rango = $limiteSuperior - $limiteInferior;

        $anchoGrafico = 680;

        $altoGrafico = 230;

        $origenX = 95;

        $origenY = 35;

        $puntos = [];

        foreach ($filas as $indice => $fila) {

            $x = $origenX + (
                $cantidad === 1
                    ? $anchoGrafico / 2
                    : $indice * $anchoGrafico / ($cantidad - 1)
            );

            $y = $origenY + (
                ($limiteSuperior - $fila['caue_total'])
                / $rango
            ) * $altoGrafico;

            $puntos[] = [
                'x' => $x,
                'y' => $y,
                'anio' => $fila['anio'],
                'caue' => $fila['caue_total'],
                'optimo' => $fila['optimo'],
            ];
        }

        $trayectoria = collect($puntos)
            ->map(fn ($p) => $p['x'].','.$p['y'])
            ->implode(' ');

    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Comportamiento del CAUE durante la vida útil

            </h5>

            <svg viewBox="0 0 850 330"
                 class="w-100"
                 role="img"
                 aria-label="Gráfica del costo anual equivalente por año">

                {{-- LINEAS HORIZONTALES --}}

                @for ($j = 0; $j <= 4; $j++)

                    @php

                        $valor = $limiteSuperior
                            - ($rango * $j / 4);

                        $y = $origenY
                            + ($altoGrafico * $j / 4);

                    @endphp

                    <line x1="{{ $origenX }}"
                          y1="{{ $y }}"
                          x2="{{ $origenX + $anchoGrafico }}"
                          y2="{{ $y }}"
                          stroke="currentColor"
                          opacity="0.15"/>

                    <text x="{{ $origenX - 10 }}"
                          y="{{ $y + 4 }}"
                          text-anchor="end"
                          fill="currentColor"
                          font-size="12">

                        ${{ number_format($valor, 2) }}

                    </text>

                @endfor

                {{-- LINEA DE COSTOS --}}

                <polyline points="{{ $trayectoria }}"
                          fill="none"
                          stroke="#198754"
                          stroke-width="3"
                          stroke-linejoin="round"
                          stroke-linecap="round"/>

                {{-- PUNTOS Y AÑOS --}}

                @foreach ($puntos as $punto)

                    <circle cx="{{ $punto['x'] }}"
                            cy="{{ $punto['y'] }}"
                            r="{{ $punto['optimo'] ? 7 : 4 }}"
                            fill="{{ $punto['optimo'] ? '#ffc107' : '#198754' }}">

                        <title>
                            Año {{ $punto['anio'] }}:
                            ${{ number_format($punto['caue'], 2) }}
                        </title>

                    </circle>

                    @if (
                        $cantidad <= 20
                        || $punto['anio'] % 5 === 0
                        || $punto['anio'] === 1
                    )

                        <text x="{{ $punto['x'] }}"
                              y="290"
                              text-anchor="middle"
                              fill="currentColor"
                              font-size="12">

                            {{ $punto['anio'] }}

                        </text>

                    @endif

                    @if ($punto['optimo'])

                        <text x="{{ $punto['x'] }}"
                              y="{{ $punto['y'] - 15 }}"
                              text-anchor="middle"
                              fill="#d99b00"
                              font-size="13"
                              font-weight="bold">

                            ${{ number_format($punto['caue'], 2) }}

                        </text>

                    @endif

                @endforeach

                <text x="435"
                      y="320"
                      text-anchor="middle"
                      fill="currentColor"
                      font-size="14">

                    Años de conservación del activo

                </text>

            </svg>

            <div class="d-flex flex-wrap gap-4
                        justify-content-center small">

                <span>

                    <i class="fa-solid fa-circle text-success me-1"></i>
                    CAUE total

                </span>

                <span>

                    <i class="fa-solid fa-circle text-warning me-1"></i>
                    Año óptimo

                </span>

            </div>

        </div>

    </div>

    {{-- DESARROLLO MATEMATICO --}}

    @php

        $optimo = collect($resultado['filas'])
            ->firstWhere('optimo', true);

    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Fórmulas y desarrollo matemático

            </h5>

            <div class="row g-3">

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            1. Valor de mercado
                        </h6>

                        <p class="text-body-secondary small">

                            VMₙ = P × (1 − d)ⁿ

                        </p>

                        <p class="font-monospace small">

                            VM = {{ number_format($resultado['inversion'], 2) }}

                            × (1 − {{ number_format($perdidaValor / 100, 4) }})

                            ^ {{ $optimo['anio'] }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($optimo['valor_mercado'], 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            2. Costo de operación
                        </h6>

                        <p class="text-body-secondary small">

                            COₙ = CO₁ × (1 + g)⁽ⁿ⁻¹⁾

                        </p>

                        <p class="font-monospace small">

                            CO = {{ number_format($resultado['costo_inicial'], 2) }}

                            × (1 + {{ number_format($crecimientoCosto / 100, 4) }})

                            ^ {{ $optimo['anio'] - 1 }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($optimo['costo_operacion'], 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            3. Valor presente de los costos
                        </h6>

                        <p class="text-body-secondary small">

                            VP = P − VM(P/F) + Σ COₜ(P/F,i,t)

                        </p>

                        <p class="font-monospace small">

                            VP = {{ number_format($resultado['inversion'], 2) }}

                            − {{ number_format($optimo['vp_mercado'], 2) }}

                            + {{ number_format($optimo['vp_costos_acumulados'], 2) }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($optimo['vp_total'], 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            4. Costo anual equivalente
                        </h6>

                        <p class="text-body-secondary small">

                            CAUE = VP × (A/P,i,n)

                        </p>

                        <p class="font-monospace small">

                            CAUE = {{ number_format($optimo['vp_total'], 2) }}

                            × {{ number_format($optimo['factor_ap'], 6) }}

                        </p>

                        <div class="fs-4 fw-bold text-success">

                            ${{ number_format($optimo['caue_total'], 2) }}

                        </div>

                    </div>

                </div>

            </div>

            <div class="mt-4">

                <h6 class="fw-bold">

                    Descomposición del CAUE mínimo

                </h6>

                <div class="table-responsive">

                    <table class="table table-bordered mb-0">

                        <tbody>

                            <tr>

                                <td>CAUE de recuperación de capital</td>

                                <td class="text-end font-monospace">

                                    ${{ number_format($optimo['caue_capital'], 2) }}

                                </td>

                            </tr>

                            <tr>

                                <td>CAUE de operación</td>

                                <td class="text-end font-monospace">

                                    ${{ number_format($optimo['caue_operacion'], 2) }}

                                </td>

                            </tr>

                            <tr class="fw-bold table-success">

                                <td>CAUE total mínimo</td>

                                <td class="text-end font-monospace">

                                    ${{ number_format($optimo['caue_total'], 2) }}

                                </td>

                            </tr>

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    {{-- INTERPRETACION FINAL --}}

    <div class="alert alert-success">

        <h5 class="alert-heading">

            <i class="fa-solid fa-circle-check me-2"></i>

            Resultado de la evaluación

        </h5>

        <p>

            Bajo los supuestos utilizados, la vida económica
            de <strong>{{ $alternativa->nombre }}</strong>
            es de

            <strong>
                {{ $resultado['vida_economica'] }} año(s)
            </strong>,

            con un costo anual equivalente mínimo de

            <strong>
                ${{ number_format($resultado['caue_minimo'], 2) }}
            </strong>.

        </p>

        @if ($resultado['vida_economica'] < $resultado['vida_util'])

            <p>

                La vida económica es menor que la vida útil
                del activo. Conviene evaluar su reemplazo
                al finalizar el año identificado como óptimo.

            </p>

        @else

            <p>

                Dentro del horizonte evaluado, el menor
                costo anual equivalente se obtiene al
                finalizar la vida útil del activo.

            </p>

        @endif

        <hr>

        <div class="small">

            Esta evaluación se realiza antes de impuestos,
            suponiendo un servicio equivalente durante
            los períodos analizados.

            El valor de mercado proyectado no equivale
            necesariamente al valor en libros ni al
            salvamento contable.

            La decisión definitiva debe considerar
            el análisis de retiro y reemplazo,
            así como la verificación de las fuentes
            de información.

        </div>

    </div>

@endsection
