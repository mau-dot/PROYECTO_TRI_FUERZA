@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Alternativas - Economía')
@section('titulo_modulo', 'Alternativas')

@section('acciones')
    @if ($decision)
        <a href="{{ route('economia.alternativas.create') }}"
           class="btn btn-success">
            <i class="fa-solid fa-plus me-1"></i>Nueva alternativa
        </a>
    @endif
@endsection

@section('modulo')

@if (! $decision || $alternativas->isEmpty())

    <div class="card border-0 shadow-sm rounded-4" style="max-width: 760px;">
        <div class="card-body p-4">

            <h5 class="fw-bold">
                <i class="fa-solid fa-code-compare me-2"></i>
                Todavía no hay alternativas
            </h5>

            <p class="text-body-secondary">
                Cada alternativa tiene su
                <strong>inversión inicial, vida útil, valor de salvamento y beneficio anual</strong>.

                Su <strong>costo de operación</strong> sale del
                <em>Sistema de costos</em> (Contabilidad).

                @unless ($decision)
                    Primero hay que registrar la
                    <a href="{{ route('economia.decision.edit') }}">
                        decisión de inversión
                    </a> (TMAR e ISR).
                @endunless
            </p>

            <p class="text-body-secondary small">
                Mientras llega la propuesta real, pueden cargar un
                <strong>ejemplo provisional</strong>:
                la decisión (TMAR 15 %, ISR 25 %) y 3 alternativas
                de estantes enlazadas a sus costeos.

                Todo queda marcado como
                <span class="badge text-bg-warning">Provisional</span>.
            </p>

            <div class="d-flex gap-2 flex-wrap">

                <form method="POST"
                      action="{{ route('economia.alternativas.cargar-ejemplo') }}">

                    @csrf

                    <button class="btn btn-warning">
                        <i class="fa-solid fa-flask me-1"></i>
                        Cargar ejemplo provisional (estantes)
                    </button>

                </form>

                <a href="{{ $decision ? route('economia.alternativas.create') : route('economia.decision.edit') }}"
                   class="btn btn-outline-secondary">

                    {{ $decision ? 'Crear una alternativa a mano' : 'Registrar la decisión' }}

                </a>

            </div>
        </div>
    </div>

@else

    <div class="mb-3">

        <h5 class="fw-bold mb-1">
            {{ $decision->titulo }}

            @if ($decision->provisional)
                <span class="badge text-bg-warning align-middle">
                    Provisional
                </span>
            @endif
        </h5>

        <span class="text-body-secondary">
            TMAR {{ rtrim(rtrim(number_format($decision->tmar, 4), '0'), '.') }} %
            · ISR {{ rtrim(rtrim(number_format($decision->tasa_isr, 2), '0'), '.') }} %
        </span>

        <a href="{{ route('economia.decision.edit') }}"
           class="small ms-2">
            Cambiar
        </a>

    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">

            <table class="table table-sm table-hover align-middle mb-0 reporte">

                <thead>
                    <tr>
                        <th class="ps-3">Alternativa</th>
                        <th class="text-end">Inversión</th>
                        <th class="text-end">Vida útil</th>
                        <th class="text-end">Salvamento</th>
                        <th class="text-end">Beneficio anual</th>
                        <th class="text-end">Costo de operación</th>
                        <th class="text-end">Flujo anual</th>
                        <th>Evidencias</th>
                        <th class="pe-3"></th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($alternativas as $a)

                        <tr>

                            <td class="ps-3 text-wrap" style="min-width: 14rem;">

                                <a href="{{ route('economia.alternativas.show', $a) }}"
                                   class="fw-semibold text-decoration-none">
                                    {{ $a->nombre }}
                                </a>

                                @if ($a->provisional)
                                    <span class="badge text-bg-warning">
                                        Provisional
                                    </span>
                                @endif

                            </td>

                            <td class="num">
                                {{ $a->formato('inversion') }}
                            </td>

                            <td class="text-end">
                                {{ $a->vida_util }} años
                            </td>

                            <td class="num">
                                {{ $a->formato('valor_salvamento') }}
                            </td>

                            <td class="num">
                                {{ $a->formato('beneficio_anual') }}
                            </td>

                            <td class="num">

                                {{ number_format($a->costoOperacion(), 2) }}

                                <div class="small text-body-secondary">

                                    @if ($a->costeo)

                                        <a href="{{ route('contabilidad.costeos.show', $a->costeo) }}"
                                           class="text-decoration-none">
                                            <i class="fa-solid fa-coins"></i>
                                            costeo
                                        </a>

                                    @else
                                        manual
                                    @endif

                                </div>
                            </td>

                            <td class="num fw-semibold {{ $a->flujoAnual() < 0 ? 'text-danger' : '' }}">

                                {{ \App\Support\Dinero::formato(\App\Support\Dinero::centavos($a->flujoAnual())) }}

                            </td>

                            <td>
                                <span class="badge text-bg-light border">
                                    <i class="fa-solid fa-paperclip"></i>
                                    {{ $a->evidencias_count }}
                                </span>
                            </td>

                            <td class="pe-3 text-end text-nowrap">

                                <a href="{{ route('economia.alternativas.show', $a) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Ver y evidencias">
                                    <i class="fa-solid fa-eye"></i>
                                </a>

                                <a href="{{ route('economia.alternativas.edit', $a) }}"
                                   class="btn btn-sm btn-outline-secondary"
                                   title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>

                            </td>

                        </tr>

                    @endforeach

                </tbody>

            </table>

        </div>
    </div>

    <p class="small text-body-secondary mt-2">
        Flujo anual = beneficio anual − costo de operación (antes de impuestos).

        El detalle año por año está en
        <a href="{{ route('economia.flujos.index') }}">
            Flujos de caja
        </a>.
    </p>

@endif

@endsection
