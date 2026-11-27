@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Decisión de inversión - Economía')
@section('titulo_modulo', 'Decisión de inversión')

@section('modulo')

@if ($decision->provisional)
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>
        Decisión <strong>provisional</strong>: la TMAR y el ISR son supuestos.
        Reemplácenlos y respáldenlos con sus fuentes.
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4" style="max-width: 820px;">
    <div class="card-body p-4">

        <form method="POST"
              action="{{ route('economia.decision.update') }}"
              novalidate>

            @csrf
            @method('PUT')

            {{-- Nombre de la decisión --}}
            <div class="mb-3">
                <label for="titulo" class="form-label">
                    Decisión <span class="text-danger">*</span>
                </label>

                <input type="text"
                       id="titulo"
                       name="titulo"
                       maxlength="200"
                       placeholder="Ej.: Renovar la estantería de la tienda"
                       class="form-control @error('titulo') is-invalid @enderror"
                       value="{{ old('titulo', $decision->titulo) }}">

                @error('titulo')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Descripción y justificación --}}
            <div class="mb-3">
                <label for="descripcion" class="form-label">
                    Descripción y justificación
                </label>

                <textarea id="descripcion"
                          name="descripcion"
                          rows="3"
                          maxlength="3000"
                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $decision->descripcion) }}</textarea>

                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="row g-3">

                {{-- TMAR --}}
                <div class="col-sm-6">

                    <label for="tmar" class="form-label">
                        TMAR (% efectiva anual)
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <input type="number"
                               id="tmar"
                               name="tmar"
                               step="0.0001"
                               min="0.0001"
                               max="100"
                               class="form-control @error('tmar') is-invalid @enderror"
                               value="{{ old('tmar', $decision->tmar) }}">

                        <span class="input-group-text">%</span>

                        @error('tmar')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-text">
                        Tasa del banco + prima de riesgo.
                        Si es nominal anual capitalizable mensualmente,
                        conviértela: (1 + r/12)¹² − 1.
                    </div>

                </div>

                {{-- ISR --}}
                <div class="col-sm-6">

                    <label for="tasa_isr" class="form-label">
                        Tasa de ISR
                        <span class="text-danger">*</span>
                    </label>

                    <div class="input-group">

                        <input type="number"
                               id="tasa_isr"
                               name="tasa_isr"
                               step="0.01"
                               min="0"
                               max="100"
                               class="form-control @error('tasa_isr') is-invalid @enderror"
                               value="{{ old('tasa_isr', $decision->tasa_isr) }}">

                        <span class="input-group-text">%</span>

                        @error('tasa_isr')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror

                    </div>

                    <div class="form-text">
                        Para la evaluación después de impuestos (fase 7).
                    </div>

                </div>

            </div>

            {{-- Identificar datos provisionales --}}
            <div class="form-check mt-3">

                <input class="form-check-input"
                       type="checkbox"
                       id="provisional"
                       name="provisional"
                       value="1"
                       @checked(old('provisional', $decision->provisional))>

                <label class="form-check-label" for="provisional">
                    Provisional (datos supuestos que se van a reemplazar)
                </label>

            </div>

            {{-- Guardar --}}
            <button type="submit" class="btn btn-success mt-4">
                <i class="fa-solid fa-floppy-disk me-1"></i>
                Guardar
            </button>

        </form>

    </div>
</div>

{{-- Evidencias de la decisión --}}
@if ($decision->exists)

    <div style="max-width: 820px;">

        @include('partials.evidencias', [
            'modelo' => $decision,
            'tipo' => 'decision',
            'campo' => 'tmar',
            'titulo' => 'Fuentes de la TMAR'
        ])

        @include('partials.evidencias', [
            'modelo' => $decision,
            'tipo' => 'decision',
            'campo' => 'tasa_isr',
            'titulo' => 'Fuentes de la tasa de ISR'
        ])

        @include('partials.evidencias', [
            'modelo' => $decision,
            'tipo' => 'decision',
            'titulo' => 'Evidencias de la decisión (entrevista, Anexo B)'
        ])

    </div>

@endif

@endsection
