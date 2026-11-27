@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Evaluación después de impuestos - Economía')
@section('titulo_modulo', 'Evaluación después de impuestos')

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
            Evaluación económica después de impuestos
        </h5>

        <div class="text-body-secondary">

            {{ $decision->titulo }}

            · TMAR {{ number_format($decision->i() * 100, 2) }} %

            · ISR {{ number_format($decision->t() * 100, 2) }} %

            · Cifras en dólares (US$)

            @if ($resultado['provisional'])

                <span class="badge text-bg-warning">
                    Datos provisionales
                </span>

            @endif

        </div>

    </div>

    @if ($resultado['provisional'])

        <div class="alert alert-warning">

            <i class="fa-solid fa-triangle-exclamation me-2"></i>

            Esta evaluación utiliza datos provisionales.
            Los valores deberán sustituirse por información
            real respaldada con evidencias.

        </div>

    @endif

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
                  action="{{ route('economia.impuestos') }}">

                <div class="row g-3">

                    <div class="col-md-6">

                        <label class="form-label">
                            Alternativa de inversión
                        </label>

                        <select name="alternativa"
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

                        <label class="form-label">
                            Método de depreciación
                        </label>

                        <select name="metodo"
                                class="form-select"
                                required>

                            @foreach ($metodos as $clave => $nombre)

                                <option value="{{ $clave }}"
                                    @selected($metodo === $clave)>

                                    {{ $nombre }}

                                </option>

                            @endforeach

                        </select>

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

                            <label class="form-check-label"
                                   for="escudo_fiscal">

                                Permitir aprovechamiento fiscal
                                de las pérdidas

                            </label>

                        </div>

                        <div class="small text-body-secondary mt-2">

                            Activar únicamente cuando se suponga
                            que las pérdidas pueden compensarse
                            con otras utilidades gravables.

                        </div>

                    </div>

                    <div class="col-12">

                        <button type="submit"
                                class="btn btn-success">

                            <i class="fa-solid fa-calculator me-1"></i>
                            Calcular

                        </button>

                        <a href="{{ route('economia.impuestos') }}"
                           class="btn btn-outline-secondary">

                            Restablecer

                        </a>

                    </div>

                </div>

            </form>

        </div>

    </div>

    {{-- INFORMACION DE LA ALTERNATIVA --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold">

                {{ $alternativa->nombre }}

            </h5>

            <div class="text-body-secondary mb-3">

                Método:
                {{ $resultado['nombre_metodo'] }}

            </div>

            <div class="row g-3">

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Inversión inicial
                        </div>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($alternativa->inversion, 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Vida útil
                        </div>

                        <div class="fs-4 fw-bold">

                            {{ $alternativa->vida_util }} años

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Valor de salvamento
                        </div>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($alternativa->valor_salvamento, 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Beneficio anual
                        </div>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($alternativa->beneficio_anual, 2) }}

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Costo de operación
                        </div>

                        <div class="fs-4 fw-bold">

                            ${{ number_format($alternativa->costoOperacion(), 2) }}

                        </div>

                        <div class="small text-body-secondary">

                            {{ $alternativa->origenCosto() }}

                        </div>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="border rounded-3 p-3 h-100">

                        <div class="small text-body-secondary">
                            Tasa de ISR
                        </div>

                        <div class="fs-4 fw-bold">

                            {{ number_format($decision->t() * 100, 2) }} %

                        </div>

                    </div>

                </div>

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

    {{-- INDICADORES ECONOMICOS --}}

    <div class="row g-3 mb-4">

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="text-body-secondary small">
                        VPN después de ISR
                    </div>

                    <div class="fs-3 fw-bold
                        {{ $resultado['vpn'] >= 0 ? 'text-success' : 'text-danger' }}">

                        ${{ number_format($resultado['vpn'], 2) }}

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="text-body-secondary small">
                        TIR después de ISR
                    </div>

                    <div class="fs-3 fw-bold">

                        {{ $resultado['tir'] === null
                            ? 'N/D'
                            : number_format($resultado['tir'] * 100, 2).' %' }}

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="text-body-secondary small">
                        Valor anual neto
                    </div>

                    <div class="fs-3 fw-bold
                        {{ $resultado['caue'] >= 0 ? 'text-success' : 'text-danger' }}">

                        ${{ number_format($resultado['caue'], 2) }}

                    </div>

                </div>

            </div>

        </div>

        <div class="col-md-6 col-xl-3">

            <div class="card border-0 shadow-sm rounded-4 h-100">

                <div class="card-body">

                    <div class="text-body-secondary small">
                        Relación beneficio/costo
                    </div>

                    <div class="fs-3 fw-bold">

                        {{ $resultado['bc'] === null
                            ? 'N/D'
                            : number_format($resultado['bc'], 3) }}

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- TABLA DE FLUJOS DESPUES DE IMPUESTOS --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Flujos de efectivo después de impuestos (FEDI)

            </h5>

            <div class="table-responsive">

                <table class="table table-bordered table-hover
                              align-middle text-end mb-0">

                    <thead>

                        <tr>

                            <th class="text-start">Año</th>
                            <th>Inversión</th>
                            <th>Beneficio</th>
                            <th>Costo</th>
                            <th>Depreciación</th>
                            <th>Utilidad gravable</th>
                            <th>ISR</th>
                            <th>Utilidad neta</th>
                            <th>Valor en libros</th>
                            <th>Salvamento neto</th>
                            <th>FEDI</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($resultado['filas'] as $fila)

                            <tr class="font-monospace">

                                <td class="text-start">

                                    {{ $fila['anio'] }}

                                </td>

                                @foreach ([
                                    'inversion',
                                    'beneficio',
                                    'costo',
                                    'depreciacion',
                                    'utilidad_gravable',
                                    'isr',
                                    'utilidad_neta',
                                    'valor_libros',
                                    'salvamento_neto'
                                ] as $campo)

                                    <td>

                                        {{ $fila[$campo] == 0
                                            ? '—'
                                            : number_format($fila[$campo], 2) }}

                                    </td>

                                @endforeach

                                <td class="fw-bold
                                    {{ $fila['fedi'] >= 0
                                        ? 'text-success'
                                        : 'text-danger' }}">

                                    {{ number_format($fila['fedi'], 2) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                    <tfoot>

                        <tr class="fw-bold">

                            <td class="text-start">
                                Total
                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'inversion'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'beneficio'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'costo'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    $resultado['total_depreciacion'], 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'utilidad_gravable'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'isr'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'utilidad_neta'
                                    )), 2
                                ) }}

                            </td>

                            <td>—</td>

                            <td>

                                {{ number_format(
                                    array_sum(array_column(
                                        $resultado['filas'], 'salvamento_neto'
                                    )), 2
                                ) }}

                            </td>

                            <td>

                                {{ number_format(
                                    array_sum($resultado['flujos']), 2
                                ) }}

                            </td>

                        </tr>

                    </tfoot>

                </table>

            </div>

            <div class="small text-body-secondary mt-3">

                Los totales de flujo representan sumas nominales,
                sin descontar. No deben confundirse con el VPN.

                El salvamento neto incluye el efecto fiscal de
                la venta del activo, cuando corresponde.

            </div>

        </div>

    </div>

    {{-- COMPARACION DE METODOS --}}

    @php
        $mejorVpn = max(array_column(
            $comparacionMetodos, 'vpn'
        ));
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Comparación de métodos de depreciación

            </h5>

            <div class="table-responsive">

                <table class="table table-hover align-middle mb-0">

                    <thead>

                        <tr>

                            <th>Método</th>
                            <th class="text-end">Depreciación total</th>
                            <th class="text-end">ISR total</th>
                            <th class="text-end">VPN</th>
                            <th class="text-end">TIR</th>
                            <th class="text-end">VA neto</th>
                            <th class="text-end">B/C</th>

                        </tr>

                    </thead>

                    <tbody>

                        @foreach ($comparacionMetodos as $clave => $r)

                            <tr class="{{ $clave === $metodo
                                ? 'table-active'
                                : '' }}">

                                <td>

                                    {{ $r['nombre_metodo'] }}

                                    @if (abs($r['vpn'] - $mejorVpn) < 0.01)

                                        <span class="badge text-bg-success">
                                            Mayor VPN
                                        </span>

                                    @endif

                                </td>

                                <td class="text-end font-monospace">

                                    {{ number_format(
                                        $r['total_depreciacion'], 2
                                    ) }}

                                </td>

                                <td class="text-end font-monospace">

                                    {{ number_format(
                                        $r['total_isr'], 2
                                    ) }}

                                </td>

                                <td class="text-end fw-bold font-monospace
                                    {{ $r['vpn'] >= 0
                                        ? 'text-success'
                                        : 'text-danger' }}">

                                    {{ number_format($r['vpn'], 2) }}

                                </td>

                                <td class="text-end font-monospace">

                                    {{ $r['tir'] === null
                                        ? 'N/D'
                                        : number_format($r['tir'] * 100, 2).' %' }}

                                </td>

                                <td class="text-end font-monospace">

                                    {{ number_format($r['caue'], 2) }}

                                </td>

                                <td class="text-end font-monospace">

                                    {{ $r['bc'] === null
                                        ? 'N/D'
                                        : number_format($r['bc'], 3) }}

                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

            <div class="small text-body-secondary mt-3">

                Los métodos distribuyen la depreciación
                de manera diferente a lo largo del tiempo.
                Esto modifica el momento en que se pagan
                los impuestos y, por tanto, el valor presente
                de los flujos.

                La comparación es económica y no implica que
                todos los métodos sean fiscalmente admisibles.

            </div>

        </div>

    </div>

    {{-- FORMULAS Y SUSTITUCION --}}

    @php
        $primerAnio = $resultado['filas'][1];
    @endphp

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">

                Desarrollo matemático del primer año

            </h5>

            <div class="row g-3">

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            1. Utilidad gravable
                        </h6>

                        <p class="text-body-secondary small">

                            UG = Beneficio − Costo − Depreciación

                        </p>

                        <p class="font-monospace">

                            UG = {{ number_format(
                                $primerAnio['beneficio'], 2
                            ) }}

                            − {{ number_format(
                                abs($primerAnio['costo']), 2
                            ) }}

                            − {{ number_format(
                                $primerAnio['depreciacion'], 2
                            ) }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format(
                                $primerAnio['utilidad_gravable'], 2
                            ) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            2. Impuesto sobre la renta
                        </h6>

                        <p class="small text-body-secondary">

                            @if ($escudoFiscal)

                                ISR = UG × tasa de ISR

                            @else

                                ISR = máximo(0, UG) × tasa de ISR

                            @endif

                        </p>

                        <p class="font-monospace">

                            ISR =

                            {{ number_format(
                                $escudoFiscal
                                    ? $primerAnio['utilidad_gravable']
                                    : max(0, $primerAnio['utilidad_gravable']),
                                2
                            ) }}

                            × {{ number_format($decision->t(), 4) }}

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format(
                                $primerAnio['isr'], 2
                            ) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            3. Utilidad después de impuestos
                        </h6>

                        <p class="small text-body-secondary">

                            UN = UG − ISR

                        </p>

                        <p class="font-monospace">

                            UN = {{ number_format(
                                $primerAnio['utilidad_gravable'], 2
                            ) }}

                            − ({{ number_format(
                                $primerAnio['isr'], 2
                            ) }})

                        </p>

                        <div class="fs-4 fw-bold">

                            ${{ number_format(
                                $primerAnio['utilidad_neta'], 2
                            ) }}

                        </div>

                    </div>

                </div>

                <div class="col-lg-6">

                    <div class="border rounded-3 p-3 h-100">

                        <h6 class="fw-bold">
                            4. Flujo después de impuestos
                        </h6>

                        <p class="small text-body-secondary">

                            FEDI = UN + Depreciación + Salvamento neto

                        </p>

                        <p class="font-monospace">

                            FEDI = {{ number_format(
                                $primerAnio['utilidad_neta'], 2
                            ) }}

                            + {{ number_format(
                                $primerAnio['depreciacion'], 2
                            ) }}

                            + {{ number_format(
                                $primerAnio['salvamento_neto'], 2
                            ) }}

                        </p>

                        <div class="fs-4 fw-bold text-success">

                            ${{ number_format(
                                $primerAnio['fedi'], 2
                            ) }}

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

    {{-- FORMULAS DE INDICADORES --}}

    <div class="card border-0 shadow-sm rounded-4 mb-4">

        <div class="card-body p-4">

            <h5 class="fw-bold mb-3">
                Indicadores después de impuestos
            </h5>

            <p>
                <strong>VPN:</strong>
                Σ FEDIₜ / (1 + TMAR)ᵗ
            </p>

            <p>
                <strong>TIR:</strong>
                tasa que hace que el VPN sea igual a cero.
            </p>

            <p>
                <strong>VA neto:</strong>
                VPN × (A/P, TMAR, n).
            </p>

            <p>
                <strong>B/C:</strong>
                valor presente de beneficios dividido
                entre el valor presente de costos,
                incluyendo impuestos y descontando
                el salvamento neto.
            </p>

            <div class="font-monospace small">

                B/C = {{ number_format(
                    $resultado['vp_beneficios'], 2
                ) }}

                / {{ number_format(
                    $resultado['vp_costos'], 2
                ) }}

            </div>

        </div>

    </div>

    {{-- CONCLUSION --}}

    <div class="alert
        {{ $resultado['aceptable']
            ? 'alert-success'
            : 'alert-danger' }}">

        <h5 class="alert-heading">

            Evaluación individual después de impuestos

        </h5>

        @if ($resultado['aceptable'])

            La alternativa alcanza o supera la rentabilidad
            mínima requerida, considerando el método de
            depreciación seleccionado y la tasa de ISR.

        @else

            La alternativa no alcanza la rentabilidad
            mínima requerida después de impuestos.

        @endif

        <hr>

        <div class="small">

            Esta conclusión corresponde únicamente a la
            evaluación individual. La selección definitiva
            debe realizarse mediante la comparación
            económica de todas las alternativas.

        </div>

    </div>

@endsection
