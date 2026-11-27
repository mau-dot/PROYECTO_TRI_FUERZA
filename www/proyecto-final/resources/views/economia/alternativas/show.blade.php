@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', $alternativa->nombre.' - Economía')
@section('titulo_modulo', 'Alternativa')

@section('acciones')

    <a href="{{ route('economia.alternativas.index') }}"
       class="btn btn-outline-secondary">
        <i class="fa-solid fa-arrow-left me-1"></i>Alternativas
    </a>

    <a href="{{ route('economia.alternativas.edit', $alternativa) }}"
       class="btn btn-outline-success">
        <i class="fa-solid fa-pen me-1"></i>Editar
    </a>

@endsection

@section('modulo')

@php
    $datos = [
        [
            'inversion',
            'Inversión inicial (P)',
            '$ '.$alternativa->formato('inversion')
        ],
        [
            'vida_util',
            'Vida útil (n)',
            $alternativa->vida_util.' años'
        ],
        [
            'valor_salvamento',
            'Valor de salvamento (VS)',
            '$ '.$alternativa->formato('valor_salvamento')
        ],
        [
            'beneficio_anual',
            'Beneficio anual',
            '$ '.$alternativa->formato('beneficio_anual')
        ],
    ];
@endphp

<h4 class="fw-bold mb-1">

    {{ $alternativa->nombre }}

    @if ($alternativa->provisional)
        <span class="badge text-bg-warning align-middle fs-6">
            Provisional
        </span>
    @endif

</h4>

<div class="text-body-secondary mb-3">
    {{ $alternativa->decision->titulo }}
</div>

@if ($alternativa->descripcion)
    <p>{{ $alternativa->descripcion }}</p>
@endif

<div class="card border-0 shadow-sm rounded-4" style="max-width: 820px;">

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <tbody>

                @foreach ($datos as [$campo, $texto, $valor])

                    <tr>

                        <td class="ps-3">
                            {{ $texto }}
                        </td>

                        <td class="text-end font-monospace fw-semibold">
                            {{ $valor }}
                        </td>

                        <td class="pe-3 text-end">

                            <a href="#ev-{{ $campo }}"
                               class="text-decoration-none">

                                @include('partials.fuentes', [
                                    'n' => $alternativa->numFuentes($campo)
                                ])

                            </a>

                        </td>

                    </tr>

                @endforeach

                {{-- Costo anual de operación --}}
                <tr>

                    <td class="ps-3">
                        Costo de operación anual
                    </td>

                    <td class="text-end font-monospace fw-semibold">
                        $ {{ number_format($alternativa->costoOperacion(), 2) }}
                    </td>

                    <td class="pe-3 text-end small">

                        @if ($alternativa->costeo)

                            <a href="{{ route('contabilidad.costeos.show', $alternativa->costeo) }}">

                                <i class="fa-solid fa-coins me-1"></i>
                                Ver costeo y sus evidencias

                            </a>

                        @else

                            <span class="text-body-secondary">
                                Ingreso manual
                            </span>

                        @endif

                    </td>

                </tr>

                {{-- Flujo anual --}}
                <tr class="table-active fw-bold">

                    <td class="ps-3">
                        Flujo anual antes de impuestos (beneficio − costo)
                    </td>

                    <td class="text-end font-monospace">

                        $ {{ \App\Support\Dinero::formato(\App\Support\Dinero::centavos($alternativa->flujoAnual())) }}

                    </td>

                    <td class="pe-3 text-end small">

                        <a href="{{ route('economia.flujos.index') }}#alt-{{ $alternativa->id }}">
                            Flujo año por año
                        </a>

                    </td>

                </tr>

            </tbody>

        </table>

    </div>

</div>

{{-- Evidencias individuales --}}
<div style="max-width: 820px;">

    @foreach ($datos as [$campo, $texto, $valor])

        <div id="ev-{{ $campo }}">

            @include('partials.evidencias', [
                'modelo' => $alternativa,
                'tipo' => 'alternativa',
                'campo' => $campo,
                'titulo' => 'Fuentes: '.$texto
            ])

        </div>

    @endforeach

    @include('partials.evidencias', [
        'modelo' => $alternativa,
        'tipo' => 'alternativa',
        'titulo' => 'Otras evidencias de la alternativa'
    ])

</div>

{{-- Eliminar alternativa --}}
<form method="POST"
      action="{{ route('economia.alternativas.destroy', $alternativa) }}"
      class="mt-3">

    @csrf
    @method('DELETE')

    <button class="btn btn-outline-danger btn-sm"
            onclick="return confirm('¿Eliminar la alternativa? (su costeo no se borra)')">

        <i class="fa-solid fa-trash me-1"></i>
        Eliminar alternativa

    </button>

</form>

@endsection