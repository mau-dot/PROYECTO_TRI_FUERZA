@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Depreciación - Economía')
@section('titulo_modulo', 'Depreciación')

@section('acciones')

    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">

        <i class="fa-solid fa-print me-1"></i>
        Imprimir

    </button>

@endsection

@section('modulo')

@if (! $alternativa)

    <div class="alert alert-info">

        Todavía no hay alternativas.

        <a href="{{ route('economia.alternativas.index') }}">
            Agrégalas
        </a>.

    </div>

@else

    {{-- Selector de alternativas --}}
    <ul class="nav nav-pills mb-3 no-print">

        @foreach ($alternativas as $a)

            <li class="nav-item">

                <a class="nav-link {{ $a->id === $alternativa->id ? 'active bg-success' : '' }}"
                   href="{{ route('economia.depreciacion', ['alternativa' => $a->id]) }}">

                    {{ $a->nombre }}

                </a>

            </li>

        @endforeach

    </ul>

    @include('economia.reportes.encabezado', [
        'titulo' => 'Cuadro de depreciación: '.$alternativa->nombre
    ])

    {{-- Datos del activo --}}
    <p class="text-center small">

        P = $ {{ $alternativa->formato('inversion') }}

        · VS = $ {{ $alternativa->formato('valor_salvamento') }}

        · n = {{ $alternativa->vida_util }} años

        · Monto a depreciar = $
        {{ number_format(
            $alternativa->inversion - $alternativa->valor_salvamento,
            2
        ) }}

    </p>

    <div class="card border-0 shadow-sm rounded-4">

        <div class="table-responsive">

            <table class="table table-sm table-bordered align-middle mb-0 reporte">

                <thead>

                    <tr>

                        <th rowspan="2"
                            class="ps-2 align-bottom">

                            Año

                        </th>

                        @foreach (\App\Services\Economia\Depreciacion::METODOS as $nombre)

                            <th colspan="3" class="text-center">

                                {{ $nombre }}

                            </th>

                        @endforeach

                    </tr>

                    <tr>

                        @foreach (\App\Services\Economia\Depreciacion::METODOS as $nombre)

                            <th class="text-end">
                                Depreciación
                            </th>

                            <th class="text-end">
                                Acumulada
                            </th>

                            <th class="text-end">
                                Valor en libros
                            </th>

                        @endforeach

                    </tr>

                </thead>

                <tbody>

                    {{-- Año cero --}}
                    <tr class="text-body-secondary">

                        <td class="ps-2">0</td>

                        @foreach (\App\Services\Economia\Depreciacion::METODOS as $m => $nombre)

                            <td></td>

                            <td></td>

                            <td class="num">

                                {{ number_format($alternativa->inversion, 2) }}

                            </td>

                        @endforeach

                    </tr>

                    {{-- Resultados anuales --}}
                    @for ($k = 0; $k < $alternativa->vida_util; $k++)

                        <tr>

                            <td class="ps-2">
                                {{ $k + 1 }}
                            </td>

                            @foreach (\App\Services\Economia\Depreciacion::METODOS as $m => $nombre)

                                <td class="num">

                                    {{ number_format(
                                        $tablas[$m][$k]['depreciacion'], 2
                                    ) }}

                                </td>

                                <td class="num">

                                    {{ number_format(
                                        $tablas[$m][$k]['acumulada'], 2
                                    ) }}

                                </td>

                                <td class="num">

                                    {{ number_format(
                                        $tablas[$m][$k]['libros'], 2
                                    ) }}

                                </td>

                            @endforeach

                        </tr>

                    @endfor

                </tbody>

                <tfoot>

                    <tr>

                        <td class="ps-2">Total</td>

                        @foreach (\App\Services\Economia\Depreciacion::METODOS as $m => $nombre)

                            <td class="num">

                                {{ number_format(
                                    array_sum(
                                        array_column($tablas[$m], 'depreciacion')
                                    ), 2
                                ) }}

                            </td>

                            <td></td>

                            <td></td>

                        @endforeach

                    </tr>

                </tfoot>

            </table>

        </div>

    </div>

    {{-- Explicación de las fórmulas --}}
    <div class="card border-0 shadow-sm rounded-4 mt-3">

        <div class="card-body small">

            <div>

                <strong>Línea recta:</strong>

                (P − VS) / n, igual todos los años.

            </div>

            <div>

                <strong>Suma de dígitos:</strong>

                (P − VS) · (n − t + 1) / [n(n + 1)/2];

                para n = {{ $alternativa->vida_util }}
                la suma de dígitos es

                {{ $alternativa->vida_util * ($alternativa->vida_util + 1) / 2 }}.

            </div>

            <div>

                <strong>Saldo doble decreciente:</strong>

                2/n × valor en libros al inicio del año,
                sin bajar del valor de salvamento;
                el último año se ajusta para cerrar en el VS.

            </div>

            <div class="mt-1 text-body-secondary">

                Los tres métodos deprecian en total lo mismo (P − VS);
                cambia <em>cuándo</em>.

                Eso afecta los impuestos de cada año (fase 7).

            </div>

        </div>

    </div>

@endif

@endsection
