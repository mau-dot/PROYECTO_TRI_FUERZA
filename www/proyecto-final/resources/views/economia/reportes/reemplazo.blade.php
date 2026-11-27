@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Retiro y reemplazo - Economía')
@section('titulo_modulo', 'Retiro y reemplazo')

@section('acciones')

    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">

        <i class="fa-solid fa-print me-1"></i>
        Imprimir

    </button>

@endsection

@section('modulo')

    {{-- ENCABEZADO DEL REPORTE --}}

    <div class="text-center mb-4">

        <h4 class="fw-bold">
            {{ $empresa?->nombre ?? 'Empresa sin registrar' }}
        </h4>

        <h5>
            Evaluación económica de retiro y reemplazo
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

        El defensor es el activo que actualmente posee
        la empresa. El retador es la alternativa que
        podría sustituirlo.

        La evaluación compara sus costos anuales
        uniformes equivalentes (CAUE).

    </div>

    <div class="alert alert-warning">

        <i class="fa-solid fa-triangle-exclamation me-2"></i>

        Los parámetros iniciales corresponden a supuestos
        provisionales. Deben respaldarse con datos reales
        y evidencias antes de tomar una decisión definitiva.

    </div>

    {{-- FORMULARIO DE EVALUACION --}}

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
                  action="{{ route('economia.reemplazo') }}">

                <div class="row g-4">

                    {{-- DEFENSOR --}}

                    <div class="col-lg-6">

                        <div class="border rounded-4 p-3 h-100">

                            <h5 class="fw-bold mb-3">

                                <i class="fa-solid fa-box me-2"></i>
                                Defensor: activo actual

                            </h5>

                            <div class="mb-3">

                                <label for="valor_mercado"
                                       class="form-label">

                                    Valor de mercado actual

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">$</span>

                                    <input type="number"
                                           id="valor_mercado"
                                           name="valor_mercado"
                                           class="form-control"
                                           min="0.01"
                                           step="0.01"
                                           value="{{ $valorMercado }}"
                                           required>

                                </div>

                                <div class="form-text">

                                    Valor por el que podría venderse
                                    actualmente el activo.

                                    No corresponde necesariamente
                                    a su costo original.

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="costo_defensor"
                                       class="form-label">

                                    Costo anual de operación

                                </label>

                                <div class="input-group">

                                    <span class="input-group-text">$</span>

                                    <input type="number"
                                           id="costo_defensor"
                                           name="costo_defensor"
                                           class="form-control"
                                           min="0"
                                           step="0.01"
                                           value="{{ $costoDefensor }}"
                                           required>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="vida_restante"
                                       class="form-label">

                                    Vida útil restante

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                           id="vida_restante"
                                           name="vida_restante"
                                           class="form-control"
                                           min="1"
                                           max="100"
                                           step="1"
                                           value="{{ $vidaRestante }}"
                                           required>

                                    <span class="input-group-text">
                                        años
                                    </span>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="perdida_defensor"
                                       class="form-label">

                                    Pérdida anual de valor de mercado

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                           id="perdida_defensor"
                                           name="perdida_defensor"
                                           class="form-control"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           value="{{ $perdidaDefensor }}"
                                           required>

                                    <span class="input-group-text">%</span>

                                </div>

                            </div>

                            <div>

                                <label for="crecimiento_defensor"
                                       class="form-label">

                                    Incremento anual del costo de operación

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                           id="crecimiento_defensor"
                                           name="crecimiento_defensor"
                                           class="form-control"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           value="{{ $crecimientoDefensor }}"
                                           required>

                                    <span class="input-group-text">%</span>

                                </div>

                            </div>

                        </div>

                    </div>

                    {{-- RETADOR --}}

                    <div class="col-lg-6">

                        <div class="border rounded-4 p-3 h-100">

                            <h5 class="fw-bold mb-3">

                                <i class="fa-solid fa-box-open me-2"></i>
                                Retador: nuevo activo

                            </h5>

                            <div class="mb-3">

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

                                <div class="form-text">

                                    Los datos se obtienen de las
                                    alternativas registradas en Economía.

                                </div>

                            </div>

                            <div class="border rounded-3 p-3 mb-3">

                                <div class="small text-body-secondary mb-2">

                                    Datos de la alternativa evaluada

                                </div>

                                <p class="mb-2">

                                    <strong>Inversión inicial:</strong>

                                    ${{ number_format($alternativa->inversion, 2) }}

                                </p>

                                <p class="mb-2">

                                    <strong>Costo de operación:</strong>

                                    ${{ number_format($alternativa->costoOperacion(), 2) }}

                                </p>

                                <p class="mb-2">

                                    <strong>Vida útil:</strong>

                                    {{ $alternativa->vida_util }} años

                                </p>

                                <p class="mb-0">

                                    <strong>Origen del costo:</strong>

                                    {{ $alternativa->origenCosto() }}

                                </p>

                            </div>

                            <div class="mb-3">

                                <label for="perdida_retador"
                                       class="form-label">

                                    Pérdida anual de valor de mercado

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                           id="perdida_retador"
                                           name="perdida_retador"
                                           class="form-control"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           value="{{ $perdidaRetador }}"
                                           required>

                                    <span class="input-group-text">%</span>

                                </div>

                            </div>

                            <div class="mb-3">

                                <label for="crecimiento_retador"
                                       class="form-label">

                                    Incremento anual del costo de operación

                                </label>

                                <div class="input-group">

                                    <input type="number"
                                           id="crecimiento_retador"
                                           name="crecimiento_retador"
                                           class="form-control"
                                           min="0"
                                           max="100"
                                           step="0.01"
                                           value="{{ $crecimientoRetador }}"
                                           required>

                                    <span class="input-group-text">%</span>

                                </div>

                            </div>

                            <a href="{{ route('economia.alternativas.show', $alternativa) }}"
                               class="btn btn-outline-secondary btn-sm">

                                <i class="fa-solid fa-paperclip me-1"></i>
                                Consultar datos y evidencias

                            </a>

                        </div>

                    </div>

                    {{-- BOTONES --}}

                    <div class="col-12">

                        <button type="submit"
                                class="btn btn-success">

                            <i class="fa-solid fa-calculator me-1"></i>
                            Evaluar reemplazo

                        </button>

                        <a href="{{ route('economia.reemplazo') }}"
                           class="btn btn-outline-secondary">

                            Restablecer

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- VARIABLES PARA PRESENTACION --}}

    @php

        $defensor = $resultado['defensor'];

        $retador = $resultado['retador'];

        $caueDefensor = $resultado['caue_defensor'];

        $caueRetador = $resultado['caue_retador'];

        $ahorro = $resultado['ahorro_anual'];

        $estado = $resultado['decision'];

        $etiquetas = [
            'reemplazar' => 'Reemplazar el activo',
            'conservar' => 'Conservar el activo',
            'indiferente' => 'Indiferencia económica',
        ];

    @endphp

    {{-- RESULTADOS PRINCIPALES --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        CAUE defensor

                    </div>

                    <div class="fs-3 fw-bold
                        {{ $estado === 'conservar' ? 'text-success' : '' }}">

                        ${{ number_format($caueDefensor, 2) }}

                    </div>

                    <div class="small text-body-secondary">

                        Vida económica:
                        {{ $defensor['vida_economica'] }} año(s)

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        CAUE retador

                    </div>

                    <div class="fs-3 fw-bold
                        {{ $estado === 'reemplazar' ? 'text-success' : '' }}">

                        ${{ number_format($caueRetador, 2) }}

                    </div>

                    <div class="small text-body-secondary">

                        Vida económica:
                        {{ $retador['vida_economica'] }} año(s)

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        Diferencia anual de costos

                    </div>

                    <div class="fs-3 fw-bold">

                        ${{ number_format(abs($ahorro), 2) }}

                    </div>

                    <div class="small text-body-secondary">

                        @if ($estado === 'reemplazar')

                            Ahorro al reemplazar

                        @elseif ($estado === 'conservar')

                            Ahorro al conservar

                        @else

                            Diferencia no significativa

                        @endif

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="small text-body-secondary">

                        Recomendación

                    </div>

                    <div class="fs-4 fw-bold text-success">

                        {{ $etiquetas[$estado] }}

                    </div>

                    <div class="small text-body-secondary">

                        Según el menor CAUE

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- TABLA COMPARATIVA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Comparación entre defensor y retador

            </h5>

            <div class="table-responsive">

                <table class="table table-bordered align-middle mb-0">

                    <thead>

                        <tr>

                            <th>Concepto</th>
                            <th class="text-end">Defensor</th>
                            <th class="text-end">Retador</th>

                        </tr>

                    </thead>

                    <tbody>

                        <tr>

                            <td>Activo evaluado</td>

                            <td class="text-end">
                                Activo actual
                            </td>

                            <td class="text-end">
                                {{ $alternativa->nombre }}
                            </td>

                        </tr>

                        <tr>

                            <td>Valor actual o inversión inicial</td>

                            <td class="text-end font-monospace">

                                {{ number_format($defensor['inversion'], 2) }}

                            </td>

                            <td class="text-end font-monospace">

                                {{ number_format($retador['inversion'], 2) }}

                            </td>

                        </tr>

                        <tr>

                            <td>Costo inicial de operación</td>

                            <td class="text-end font-monospace">

                                {{ number_format($defensor['costo_inicial'], 2) }}

                            </td>

                            <td class="text-end font-monospace">

                                {{ number_format($retador['costo_inicial'], 2) }}

                            </td>

                        </tr>

                        <tr>

                            <td>Vida útil restante o total</td>

                            <td class="text-end">

                                {{ $defensor['vida_restante'] }} años

                            </td>

                            <td class="text-end">

                                {{ $retador['vida_util'] }} años

                            </td>

                        </tr>

                        <tr>

                            <td>Pérdida anual de valor</td>

                            <td class="text-end">

                                {{ number_format($defensor['perdida_valor'] * 100, 2) }} %

                            </td>

                            <td class="text-end">

                                {{ number_format($retador['perdida_valor'] * 100, 2) }} %

                            </td>

                        </tr>

                        <tr>

                            <td>Incremento anual de costos</td>

                            <td class="text-end">

                                {{ number_format($defensor['crecimiento_costo'] * 100, 2) }} %

                            </td>

                            <td class="text-end">

                                {{ number_format($retador['crecimiento_costo'] * 100, 2) }} %

                            </td>

                        </tr>

                        <tr>

                            <td>Vida económica óptima</td>

                            <td class="text-end fw-bold">

                                {{ $defensor['vida_economica'] }} año(s)

                            </td>

                            <td class="text-end fw-bold">

                                {{ $retador['vida_economica'] }} año(s)

                            </td>

                        </tr>

                        <tr>

                            <td>Valor de mercado en el año óptimo</td>

                            <td class="text-end font-monospace">

                                {{ number_format($defensor['valor_mercado_optimo'], 2) }}

                            </td>

                            <td class="text-end font-monospace">

                                {{ number_format($retador['valor_mercado_optimo'], 2) }}

                            </td>

                        </tr>

                        <tr class="fw-bold">

                            <td>CAUE mínimo</td>

                            <td class="text-end font-monospace
                                {{ $estado === 'conservar' ? 'table-success' : '' }}">

                                {{ number_format($caueDefensor, 2) }}

                            </td>

                            <td class="text-end font-monospace
                                {{ $estado === 'reemplazar' ? 'table-success' : '' }}">

                                {{ number_format($caueRetador, 2) }}

                            </td>

                        </tr>

                    </tbody>

                </table>

            </div>

        </div>

    </div>

    {{-- GRAFICA COMPARATIVA --}}

    @php

        $maximoCaue = max($caueDefensor, $caueRetador, 1);

    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">

                Comparación gráfica del CAUE mínimo

            </h5>

            @foreach ([
                ['Defensor', $caueDefensor, $estado === 'conservar'],
                ['Retador', $caueRetador, $estado === 'reemplazar']
            ] as $barra)

                <div class="mb-4">

                    <div class="d-flex justify-content-between mb-2">

                        <span class="fw-semibold">

                            {{ $barra[0] }}

                        </span>

                        <span class="font-monospace fw-bold">

                            ${{ number_format($barra[1], 2) }}

                        </span>

                    </div>

                    <div class="progress" style="height: 30px;">

                        <div class="progress-bar
                            {{ $barra[2] ? 'bg-success' : 'bg-secondary' }}"

                            role="progressbar"

                            aria-label="{{ $barra[0] }}"

                            style="width: {{ $barra[1] / $maximoCaue * 100 }}%">

                        </div>

                    </div>

                </div>

            @endforeach

            <div class="small text-body-secondary">

                El menor costo anual equivalente representa
                la opción más conveniente bajo el supuesto
                de servicio equivalente.

            </div>

        </div>

    </div>

    {{-- TABLAS DE PROYECCION --}}

    @foreach ([
        ['Defensor: activo actual', $defensor],
        ['Retador: '.$alternativa->nombre, $retador]
    ] as $grupo)

        <div class="card border-0 shadow-sm rounded-4 mb-4">

            <div class="card-body p-4">

                <h5 class="fw-bold mb-3">

                    Proyección del {{ $grupo[0] }}

                </h5>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover
                                  align-middle text-end mb-0">

                        <thead>

                            <tr>

                                <th class="text-start">Año</th>
                                <th>Valor de mercado</th>
                                <th>Costo de operación</th>
                                <th>CAUE capital</th>
                                <th>CAUE operación</th>
                                <th>CAUE total</th>
                                <th class="text-center">Evaluación</th>

                            </tr>

                        </thead>

                        <tbody>

                            @foreach ($grupo[1]['filas'] as $fila)

                                <tr class="font-monospace
                                    {{ $fila['optimo'] ? 'table-success fw-bold' : '' }}">

                                    <td class="text-start">

                                        {{ $fila['anio'] }}

                                    </td>

                                    @foreach ([
                                        'valor_mercado',
                                        'costo_operacion',
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

            </div>

        </div>

    @endforeach

    {{-- FORMULAS Y DESARROLLO MATEMATICO --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Fórmulas y desarrollo matemático

            </h5>

            <div class="border rounded-3 p-3 mb-3">

                <h6 class="fw-bold">

                    Costo anual uniforme equivalente

                </h6>

                <p class="font-monospace small mb-2">

                    CAUE(n) = [P − VMₙ(P/F,i,n)
                    + Σ COₜ(P/F,i,t)] × (A/P,i,n)

                </p>

                <div class="small text-body-secondary">

                    P representa la inversión inicial del retador
                    o el valor de mercado actual del defensor.

                </div>

            </div>

            <div class="row g-3">

                @foreach ([
                    ['Defensor', $defensor],
                    ['Retador', $retador]
                ] as $grupo)

                    @php

                        $optimo = collect($grupo[1]['filas'])
                            ->firstWhere('optimo', true);

                    @endphp

                    <div class="col-lg-6">

                        <div class="border rounded-3 p-3 h-100">

                            <h6 class="fw-bold">

                                CAUE mínimo del {{ $grupo[0] }}

                            </h6>

                            <p class="small text-body-secondary">

                                Año óptimo: {{ $optimo['anio'] }}

                            </p>

                            <p class="font-monospace small">

                                VP = {{ number_format($grupo[1]['inversion'], 2) }}

                                − {{ number_format($optimo['vp_mercado'], 2) }}

                                + {{ number_format($optimo['vp_costos_acumulados'], 2) }}

                            </p>

                            <p class="font-monospace small">

                                VP = {{ number_format($optimo['vp_total'], 2) }}

                            </p>

                            <p class="font-monospace small">

                                CAUE = {{ number_format($optimo['vp_total'], 2) }}

                                × {{ number_format($optimo['factor_ap'], 6) }}

                            </p>

                            <div class="fs-4 fw-bold">

                                ${{ number_format($optimo['caue_total'], 2) }}

                            </div>

                        </div>

                    </div>

                @endforeach

                <div class="col-12">

                    <div class="border rounded-3 p-3">

                        <h6 class="fw-bold">

                            Diferencia anual equivalente

                        </h6>

                        <p class="small text-body-secondary">

                            Diferencia = CAUE defensor − CAUE retador

                        </p>

                        <p class="font-monospace">

                            Diferencia = {{ number_format($caueDefensor, 2) }}

                            − {{ number_format($caueRetador, 2) }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($ahorro, 2) }}

                        </div>

                        <hr>

                        <p class="mb-0 small">

                            Si la diferencia es positiva, el retador
                            presenta un menor costo anual.

                            Si es negativa, el defensor presenta
                            un menor costo anual.

                            Una diferencia inferior a un centavo
                            en valor absoluto se considera
                            indiferencia económica.

                        </p>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- RECOMENDACION FINAL --}}

    <div class="alert
        {{ $estado === 'indiferente' ? 'alert-info' : 'alert-success' }}">

        <h5 class="alert-heading">

            <i class="fa-solid fa-circle-check me-2"></i>

            {{ $etiquetas[$estado] }}

        </h5>

        <p>

            {{ $resultado['mensaje'] }}

        </p>

        @if ($estado === 'reemplazar')

            <p>

                El ahorro anual equivalente al reemplazar
                el activo es de

                <strong>

                    ${{ number_format($ahorro, 2) }}

                </strong>.

            </p>

            @if ($resultado['porcentaje_ahorro'] !== null)

                <p>

                    Esto representa una reducción del

                    <strong>

                        {{ number_format($resultado['porcentaje_ahorro'], 2) }} %

                    </strong>

                    respecto al CAUE del defensor.

                </p>

            @endif

        @elseif ($estado === 'conservar')

            <p>

                Conservar el activo representa un ahorro
                anual equivalente de

                <strong>

                    ${{ number_format(abs($ahorro), 2) }}

                </strong>

                frente al retador.

            </p>

        @else

            <p>

                Los costos son prácticamente equivalentes.
                Será necesario considerar otros factores
                técnicos y operativos.

            </p>

        @endif

        <hr>

        <div class="small">

            La evaluación se realiza antes de impuestos
            mediante la comparación de los CAUE mínimos.

            Se supone que ambos activos prestan un servicio
            equivalente y que las condiciones de sustitución
            permiten comparar sus costos anuales.

            La recomendación es provisional y deberá
            verificarse con las fuentes de información,
            el horizonte de servicio y los costos reales
            de sustitución.

        </div>

    </div>

@endsection
