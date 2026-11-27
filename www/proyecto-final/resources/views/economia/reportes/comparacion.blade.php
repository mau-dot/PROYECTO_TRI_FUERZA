@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Comparación de alternativas - Economía')
@section('titulo_modulo', 'Comparación de alternativas')

@section('acciones')
    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">
        <i class="fa-solid fa-print me-1"></i>
        Imprimir
    </button>
@endsection

@section('modulo')

    @php
        $recomendada = $resultado['recomendada'];
        $ranking = $resultado['ranking'];
        $pasos = $resultado['pasos'];

        $etiquetas = [
            'aceptar' => 'Aceptar',
            'descartar' => 'Descartar',
            'indiferente' => 'Indiferente',
        ];

        $colores = [
            'aceptar' => 'success',
            'descartar' => 'danger',
            'indiferente' => 'secondary',
        ];

        $moneda = fn ($valor) =>
            ($valor < 0 ? '-$' : '$')
            .number_format(abs($valor), 2);

        $porcentaje = fn ($valor) =>
            $valor === null
                ? 'N/D'
                : number_format($valor * 100, 2).' %';

        $maximoVA = max(
            1,
            ...array_map(
                fn ($a) => abs($a['caue']),
                $ranking
            )
        );
    @endphp

    {{-- ENCABEZADO DEL REPORTE --}}

    <div class="text-center mb-4">
        <h4 class="fw-bold">
            {{ $empresa?->nombre ?? 'Empresa sin registrar' }}
        </h4>

        <h5>
            Evaluación comparativa de alternativas de inversión
        </h5>

        <div class="text-body-secondary">
            {{ $decision->titulo }}
            · TMAR {{ $porcentaje($resultado['tmar']) }}
            · Cifras en dólares (US$)

            @if ($resultado['provisional'])
                <span class="badge text-bg-warning">
                    Datos provisionales
                </span>
            @endif
        </div>

        <div class="mt-2">
            @if ($despuesImpuestos)
                Evaluación después de impuestos
                · ISR {{ $porcentaje($resultado['tasa_isr']) }}
                · {{ $metodos[$metodo] }}
            @else
                Evaluación antes de impuestos
            @endif
        </div>
    </div>

    @if ($resultado['provisional'])
        <div class="alert alert-warning">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>

            La recomendación utiliza información provisional.
            Los datos deberán sustituirse por información real
            y respaldarse con evidencias antes de presentar
            una conclusión definitiva.
        </div>
    @endif

    <div class="alert alert-info">
        <i class="fa-solid fa-circle-info me-2"></i>

        Debido a que las alternativas pueden tener diferentes
        vidas útiles, la comparación utiliza el valor anual
        equivalente neto, suponiendo que las inversiones
        pueden repetirse en condiciones económicas equivalentes.
    </div>

    {{-- FORMULARIO DE PARAMETROS --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4 d-print-none">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                <i class="fa-solid fa-sliders me-2"></i>
                Parámetros de comparación
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
                  action="{{ route('economia.comparacion') }}">

                <div class="row g-3">

                    <div class="col-md-6">
                        <label for="despues_impuestos"
                               class="form-label">
                            Tipo de evaluación
                        </label>

                        <select name="despues_impuestos"
                                id="despues_impuestos"
                                class="form-select">

                            <option value="0"
                                @selected(! $despuesImpuestos)>
                                Antes de impuestos
                            </option>

                            <option value="1"
                                @selected($despuesImpuestos)>
                                Después de impuestos
                            </option>
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="metodo"
                               class="form-label">
                            Método de depreciación
                        </label>

                        <select name="metodo"
                                id="metodo"
                                class="form-select">

                            @foreach ($metodos as $clave => $nombre)
                                <option value="{{ $clave }}"
                                    @selected($metodo === $clave)>
                                    {{ $nombre }}
                                </option>
                            @endforeach
                        </select>

                        <div class="form-text">
                            Solo afecta los cálculos después de impuestos.
                        </div>
                    </div>

                    <div class="col-12">

                        <input type="hidden"
                               name="escudo_fiscal"
                               value="0">

                        <div class="form-check form-switch">

                            <input type="checkbox"
                                   class="form-check-input"
                                   id="escudo_fiscal"
                                   name="escudo_fiscal"
                                   value="1"
                                   @checked($escudoFiscal)>

                            <label for="escudo_fiscal"
                                   class="form-check-label">
                                Permitir aprovechamiento fiscal
                                de las pérdidas
                            </label>
                        </div>

                        <div class="small text-body-secondary mt-2">
                            Este supuesto solo se aplica en la
                            evaluación después de impuestos.
                        </div>
                    </div>

                    <div class="col-12">
                        <button type="submit"
                                class="btn btn-success">
                            <i class="fa-solid fa-calculator me-1"></i>
                            Comparar alternativas
                        </button>

                        <a href="{{ route('economia.comparacion') }}"
                           class="btn btn-outline-secondary">
                            Restablecer
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- RESUMEN GENERAL --}}

    <div class="row g-3 mb-4">

        @foreach ([
            ['Alternativas evaluadas', count($resultado['alternativas'])],
            ['TMAR', $porcentaje($resultado['tmar'])],
            ['ISR aplicado', $despuesImpuestos
                ? $porcentaje($resultado['tasa_isr'])
                : 'No aplicado'],
            ['Mayor valor anual neto', $recomendada
                ? $moneda($recomendada['caue'])
                : '$0.00']
        ] as $dato)

            <div class="col-md-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 h-100">
                    <div class="card-body">
                        <div class="small text-body-secondary">
                            {{ $dato[0] }}
                        </div>

                        <div class="fs-3 fw-bold">
                            {{ $dato[1] }}
                        </div>
                    </div>
                </div>
            </div>

        @endforeach
    </div>

    {{-- ALTERNATIVA RECOMENDADA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <div class="small text-body-secondary mb-2">
                <i class="fa-solid fa-trophy me-2"></i>
                Alternativa recomendada
            </div>

            @if ($recomendada)

                <h3 class="fw-bold text-success mb-3">
                    {{ $recomendada['nombre'] }}
                </h3>

                <div class="row g-3">

                    @foreach ([
                        ['Inversión inicial', $moneda($recomendada['inversion'])],
                        ['Vida útil', $recomendada['vida_util'].' años'],
                        ['VPN', $moneda($recomendada['vpn'])],
                        ['TIR', $porcentaje($recomendada['tir'])],
                        ['Valor anual neto', $moneda($recomendada['caue'])],
                        ['B/C', $recomendada['bc'] === null
                            ? 'N/D'
                            : number_format($recomendada['bc'], 3)]
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

                <div class="mt-3">
                    <a href="{{ route('economia.alternativas.show', $recomendada['id']) }}"
                       class="btn btn-outline-success">

                        <i class="fa-solid fa-paperclip me-1"></i>
                        Consultar alternativa y evidencias
                    </a>
                </div>

            @else

                <h3 class="fw-bold">
                    No realizar la inversión
                </h3>

                <p class="text-body-secondary mb-0">
                    Ninguna alternativa genera un valor anual
                    equivalente neto positivo a la TMAR establecida.
                </p>

            @endif
        </div>
    </div>

    {{-- TABLA GENERAL DE INDICADORES --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Resumen comparativo de indicadores económicos
            </h5>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">

                    <thead>
                        <tr>
                            <th>Alternativa</th>
                            <th class="text-end">Inversión</th>
                            <th class="text-end">Vida útil</th>
                            <th class="text-end">VPN</th>
                            <th class="text-end">TIR</th>
                            <th class="text-end">VA neto</th>
                            <th class="text-end">B/C</th>
                            <th>Evaluación</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($resultado['alternativas'] as $a)

                            <tr class="{{ ($recomendada['id'] ?? null) === $a['id']
                                ? 'table-success'
                                : '' }}">

                                <td>
                                    <a href="{{ route('economia.alternativas.show', $a['id']) }}"
                                       class="fw-semibold text-decoration-none">

                                        {{ $a['nombre'] }}
                                    </a>

                                    @if ($a['provisional'])
                                        <span class="badge text-bg-warning">
                                            Provisional
                                        </span>
                                    @endif

                                    <div class="small text-body-secondary">
                                        {{ $a['origen_costo'] }}
                                    </div>
                                </td>

                                <td class="text-end font-monospace">
                                    {{ $moneda($a['inversion']) }}
                                </td>

                                <td class="text-end">
                                    {{ $a['vida_util'] }} años
                                </td>

                                <td class="text-end fw-bold font-monospace
                                    {{ $a['vpn'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $moneda($a['vpn']) }}
                                </td>

                                <td class="text-end font-monospace">
                                    {{ $porcentaje($a['tir']) }}
                                </td>

                                <td class="text-end fw-bold font-monospace
                                    {{ $a['caue'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $moneda($a['caue']) }}
                                </td>

                                <td class="text-end font-monospace">
                                    {{ $a['bc'] === null
                                        ? 'N/D'
                                        : number_format($a['bc'], 3) }}
                                </td>

                                <td>
                                    @if (($recomendada['id'] ?? null) === $a['id'])

                                        <span class="badge text-bg-success">
                                            Recomendada
                                        </span>

                                    @elseif ($a['aceptable'])

                                        <span class="badge text-bg-secondary">
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

            <div class="small text-body-secondary mt-3">
                Una alternativa individualmente aceptable no
                necesariamente es la mejor inversión disponible.
                La selección final se obtiene mediante
                la comparación incremental.
            </div>

        </div>
    </div>

    {{-- CLASIFICACION ECONOMICA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Clasificación por valor anual equivalente
            </h5>

            <div class="table-responsive mb-4">

                <table class="table table-bordered align-middle">

                    <thead>
                        <tr>
                            <th>Posición</th>
                            <th>Alternativa</th>
                            <th class="text-end">VPN</th>
                            <th class="text-end">Valor anual neto</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($ranking as $indice => $a)

                            <tr>
                                <td>{{ $indice + 1 }}</td>

                                <td>
                                    {{ $a['nombre'] }}

                                    @if (($recomendada['id'] ?? null) === $a['id'])
                                        <i class="fa-solid fa-trophy text-warning ms-2"></i>
                                    @endif
                                </td>

                                <td class="text-end font-monospace">
                                    {{ $moneda($a['vpn']) }}
                                </td>

                                <td class="text-end fw-bold font-monospace
                                    {{ $a['caue'] >= 0 ? 'text-success' : 'text-danger' }}">

                                    {{ $moneda($a['caue']) }}
                                </td>
                            </tr>

                        @endforeach
                    </tbody>

                </table>
            </div>

            {{-- GRAFICA DE BARRAS CON EJE CERO --}}

            <h6 class="fw-bold mb-3">
                Comparación gráfica del valor anual neto
            </h6>

            @foreach ($ranking as $a)

                @php
                    $ancho = abs($a['caue']) / $maximoVA * 50;

                    $inicio = $a['caue'] >= 0
                        ? 50
                        : 50 - $ancho;
                @endphp

                <div class="mb-4">

                    <div class="d-flex justify-content-between mb-2 gap-3">

                        <span class="fw-semibold">
                            {{ $a['nombre'] }}
                        </span>

                        <span class="fw-bold font-monospace">
                            {{ $moneda($a['caue']) }}
                        </span>
                    </div>

                    <div class="position-relative bg-body-secondary rounded"
                         style="height: 28px;">

                        <div class="position-absolute border-start border-secondary"
                             style="left: 50%; height: 100%;">
                        </div>

                        <div class="position-absolute rounded
                            {{ $a['caue'] >= 0 ? 'bg-success' : 'bg-danger' }}"
                             style="left: {{ $inicio }}%;
                                    width: {{ $ancho }}%;
                                    height: 100%;">
                        </div>

                    </div>
                </div>

            @endforeach

            <div class="d-flex justify-content-between small text-body-secondary">
                <span>Valor negativo</span>
                <span>$0.00</span>
                <span>Valor positivo</span>
            </div>

        </div>
    </div>

    {{-- ANALISIS INCREMENTAL --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                <i class="fa-solid fa-code-compare me-2"></i>
                Análisis incremental
            </h5>

            <p class="text-body-secondary">
                Las alternativas se analizan de menor a mayor
                inversión inicial. Cada retador se compara
                contra la mejor alternativa aceptada hasta
                ese momento.
            </p>

            <div class="table-responsive">

                <table class="table table-bordered table-hover align-middle">

                    <thead>
                        <tr>
                            <th>Paso</th>
                            <th>Defensor</th>
                            <th>Retador</th>
                            <th class="text-end">Inversión adicional</th>
                            <th class="text-end">TIR incremental</th>
                            <th class="text-end">VA incremental</th>
                            <th>Decisión</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach ($pasos as $indice => $paso)

                            <tr>
                                <td>{{ $indice + 1 }}</td>

                                <td>{{ $paso['defensor'] }}</td>

                                <td>{{ $paso['retador'] }}</td>

                                <td class="text-end font-monospace">
                                    {{ $moneda($paso['inversion_incremental']) }}
                                </td>

                                <td class="text-end font-monospace">
                                    {{ $porcentaje($paso['tir_incremental']) }}
                                </td>

                                <td class="text-end fw-bold font-monospace
                                    {{ $paso['caue_incremental'] >= 0
                                        ? 'text-success'
                                        : 'text-danger' }}">

                                    {{ $moneda($paso['caue_incremental']) }}
                                </td>

                                <td>
                                    <span class="badge text-bg-{{ $colores[$paso['decision']] }}">
                                        {{ $etiquetas[$paso['decision']] }}
                                    </span>
                                </td>
                            </tr>

                        @endforeach

                    </tbody>
                </table>
            </div>

            <div class="small text-body-secondary">
                La decisión se basa en el valor anual equivalente
                incremental a la TMAR. La TIR incremental
                se presenta como información complementaria.
            </div>

        </div>
    </div>

    {{-- DESARROLLO DE CADA COMPARACION --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-4">
                Desarrollo del procedimiento incremental
            </h5>

            @foreach ($pasos as $indice => $paso)

                @php
                    $datosDefensor = collect($resultado['alternativas'])
                        ->firstWhere('nombre', $paso['defensor']);

                    $datosRetador = collect($resultado['alternativas'])
                        ->firstWhere('nombre', $paso['retador']);

                    $inversionDefensor = $datosDefensor['inversion'] ?? 0;

                    $inversionRetador = $datosRetador['inversion'] ?? 0;

                    $vaDefensor = $datosDefensor['caue'] ?? 0;

                    $vaRetador = $datosRetador['caue'] ?? 0;
                @endphp

                <div class="border rounded-4 p-3 mb-3">

                    <div class="d-flex justify-content-between
                                align-items-center flex-wrap gap-2 mb-3">

                        <h6 class="fw-bold mb-0">
                            Paso {{ $indice + 1 }}
                        </h6>

                        <span class="badge text-bg-{{ $colores[$paso['decision']] }}">
                            {{ $etiquetas[$paso['decision']] }}
                        </span>
                    </div>

                    <p>
                        <strong>Defensor:</strong>
                        {{ $paso['defensor'] }}
                    </p>

                    <p>
                        <strong>Retador:</strong>
                        {{ $paso['retador'] }}
                    </p>

                    <div class="row g-3">

                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">

                                <h6 class="fw-bold">
                                    Inversión incremental
                                </h6>

                                <p class="small text-body-secondary">
                                    ΔP = P retador − P defensor
                                </p>

                                <p class="font-monospace">
                                    ΔP = {{ number_format($inversionRetador, 2) }}
                                    − {{ number_format($inversionDefensor, 2) }}
                                </p>

                                <div class="fs-4 fw-bold">
                                    {{ $moneda($paso['inversion_incremental']) }}
                                </div>

                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="border rounded-3 p-3 h-100">

                                <h6 class="fw-bold">
                                    Valor anual incremental
                                </h6>

                                <p class="small text-body-secondary">
                                    ΔVA = VA retador − VA defensor
                                </p>

                                <p class="font-monospace">
                                    ΔVA = {{ number_format($vaRetador, 2) }}
                                    − ({{ number_format($vaDefensor, 2) }})
                                </p>

                                <div class="fs-4 fw-bold
                                    {{ $paso['caue_incremental'] >= 0
                                        ? 'text-success'
                                        : 'text-danger' }}">

                                    {{ $moneda($paso['caue_incremental']) }}
                                </div>

                            </div>
                        </div>

                        <div class="col-12">
                            <div class="border rounded-3 p-3">

                                <h6 class="fw-bold">
                                    Tasa interna de rendimiento incremental
                                </h6>

                                <p class="small text-body-secondary">
                                    La tasa de cruce iguala los valores
                                    anuales equivalentes del defensor y
                                    del retador.
                                </p>

                                <div class="row g-3">

                                    <div class="col-md-6">

                                        <div class="small text-body-secondary">
                                            TIR incremental
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            {{ $porcentaje($paso['tir_incremental']) }}
                                        </div>

                                    </div>

                                    <div class="col-md-6">

                                        <div class="small text-body-secondary">
                                            TMAR
                                        </div>

                                        <div class="fs-4 fw-bold">
                                            {{ $porcentaje($resultado['tmar']) }}
                                        </div>

                                    </div>

                                </div>

                                @if (count($paso['tasas_cruce']) > 1)

                                    <div class="alert alert-warning mt-3 mb-0">

                                        Se detectaron múltiples tasas de cruce:

                                        @foreach ($paso['tasas_cruce'] as $tasa)
                                            {{ $porcentaje($tasa) }}
                                            @if (! $loop->last)
                                                ·
                                            @endif
                                        @endforeach

                                    </div>

                                @elseif ($paso['tir_incremental'] === null)

                                    <div class="small text-body-secondary mt-3">

                                        No se encontró una tasa de cruce
                                        única dentro del intervalo evaluado.
                                        La decisión se mantiene basada
                                        en el valor anual incremental.

                                    </div>

                                @endif

                            </div>
                        </div>

                    </div>

                    <div class="alert alert-{{ $colores[$paso['decision']] }} mt-3 mb-0">

                        {{ $paso['mensaje'] }}

                    </div>

                </div>

            @endforeach

        </div>
    </div>

    {{-- CRITERIOS Y FORMULAS --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Fundamentos de la evaluación económica
            </h5>

            <div class="row g-3">

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            Valor presente neto
                        </h6>

                        <p class="font-monospace">
                            VPN = Σ FEₜ / (1 + i)ᵗ
                        </p>

                        <p class="small mb-0">
                            Incluye la inversión inicial como flujo
                            negativo del año cero. Una inversión
                            con VPN positivo genera valor por encima
                            de la rentabilidad mínima exigida.
                        </p>

                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            Valor anual equivalente neto
                        </h6>

                        <p class="font-monospace">
                            VA = VPN × (A/P, i, n)
                        </p>

                        <p class="small mb-0">
                            Permite expresar el resultado en una
                            cantidad anual equivalente y comparar
                            alternativas de diferente duración
                            bajo el supuesto de repetibilidad.
                        </p>

                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            Análisis incremental
                        </h6>

                        <p class="font-monospace">
                            ΔVA = VA retador − VA defensor
                        </p>

                        <p class="small mb-0">
                            Si el resultado es positivo, el retador
                            genera un mayor valor anual. Si es
                            negativo, se conserva el defensor.
                        </p>

                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            Relación beneficio/costo
                        </h6>

                        <p class="font-monospace">
                            B/C = VP beneficios / VP costos
                        </p>

                        <p class="small mb-0">
                            Una relación superior a uno indica
                            que los beneficios presentes superan
                            los costos presentes según la
                            convención de cálculo utilizada.
                        </p>

                    </div>
                </div>

            </div>

        </div>
    </div>

    {{-- CONCLUSION FINAL --}}

    <div class="alert {{ $recomendada ? 'alert-success' : 'alert-warning' }}">

        <h5 class="alert-heading">
            <i class="fa-solid fa-clipboard-check me-2"></i>
            Conclusión de la evaluación
        </h5>

        @if ($recomendada)

            <p>
                Bajo los parámetros y supuestos utilizados,
                se recomienda seleccionar
                <strong>{{ $recomendada['nombre'] }}</strong>,
                debido a que presenta el mayor valor anual
                equivalente neto positivo.
            </p>

            <p>
                Su inversión inicial es de
                <strong>{{ $moneda($recomendada['inversion']) }}</strong>,

                con un VPN de
                <strong>{{ $moneda($recomendada['vpn']) }}</strong>,

                y un valor anual equivalente neto de
                <strong>{{ $moneda($recomendada['caue']) }}</strong>.
            </p>

        @else

            <p>
                No se recomienda realizar ninguna de las inversiones,
                debido a que ninguna genera un valor anual
                equivalente neto positivo a la TMAR establecida.
            </p>

        @endif

        <hr>

        <p class="small mb-2">
            <strong>Criterio:</strong>
            {{ $resultado['criterio'] }}
        </p>

        <p class="small mb-2">
            <strong>Supuesto:</strong>
            {{ $resultado['supuesto'] }}
        </p>

        @if ($resultado['provisional'])

            <p class="small mb-0">
                La conclusión es provisional. La recomendación
                definitiva debe sustentarse con información
                contable real y evidencias verificables.
            </p>

        @endif

    </div>

@endsection
