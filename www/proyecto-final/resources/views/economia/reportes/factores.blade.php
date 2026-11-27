@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Factores y equivalencias - Economía')
@section('titulo_modulo', 'Factores y equivalencias')

@section('acciones')

    <button type="button"
            class="btn btn-outline-secondary"
            onclick="window.print()">

        <i class="fa-solid fa-print me-1"></i>
        Imprimir

    </button>

@endsection

@section('modulo')

<form method="GET"
      class="row g-2 align-items-end mb-3 no-print"
      style="max-width: 560px;">

    <div class="col">

        <label for="i" class="form-label small">
            Tasa i (% por periodo)
        </label>

        <input type="number"
               id="i"
               name="i"
               step="0.0001"
               min="0.01"
               max="100"
               class="form-control"
               value="{{ $tasa }}">

    </div>

    <div class="col">

        <label for="n" class="form-label small">
            Hasta n =
        </label>

        <input type="number"
               id="n"
               name="n"
               step="1"
               min="1"
               max="60"
               class="form-control"
               value="{{ $hasta }}">

    </div>

    <div class="col-auto">

        <button class="btn btn-success">
            Calcular
        </button>

        @if ($decision)

            <a href="{{ route('economia.factores') }}"
               class="btn btn-outline-secondary">

                TMAR

            </a>

        @endif

    </div>

</form>

@include('economia.reportes.encabezado', [
    'titulo' => 'Tabla de factores de interés compuesto al '.
        rtrim(rtrim(number_format($tasa, 4), '0'), '.').' %'
])

<div class="card border-0 shadow-sm rounded-4">

    <div class="table-responsive">

        <table class="table table-sm table-hover align-middle mb-0 reporte">

            <thead>

                <tr>

                    <th class="ps-3">n</th>

                    @foreach (\App\Services\Economia\Factores::NOMBRES as [$sigla, $nombre, $formula])

                        <th class="text-end"
                            title="{{ $nombre }}: {{ $formula }}">

                            ({{ $sigla }})

                        </th>

                    @endforeach

                </tr>

            </thead>

            <tbody>

                @foreach ($filas as $n => $f)

                    <tr>

                        <td class="ps-3 fw-semibold">
                            {{ $n }}
                        </td>

                        @foreach (array_keys(\App\Services\Economia\Factores::NOMBRES) as $k)

                            <td class="num">

                                {{ number_format($f[$k], 4) }}

                            </td>

                        @endforeach

                    </tr>

                @endforeach

            </tbody>

        </table>

    </div>

</div>

<div class="card border-0 shadow-sm rounded-4 mt-3">

    <div class="card-body small">

        <div class="fw-semibold mb-2">

            Fórmulas (la app usa las exactas;
            esta tabla muestra 4 decimales)

        </div>

        <div class="row">

            @foreach (\App\Services\Economia\Factores::NOMBRES as [$sigla, $nombre, $formula])

                <div class="col-md-6 mb-1">

                    <strong>({{ $sigla }}, i, n)</strong>

                    {{ $nombre }}:

                    <span class="font-monospace">
                        {{ $formula }}
                    </span>

                </div>

            @endforeach

        </div>

    </div>

</div>

@endsection
