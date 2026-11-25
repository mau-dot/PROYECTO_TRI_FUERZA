{{-- Formulario para crear y editar un costeo (una alternativa) --}}
@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', ($costeo->exists ? 'Editar' : 'Nuevo').' costeo - Contabilidad')
@section('titulo_modulo', $costeo->exists ? 'Editar costeo' : 'Nuevo costeo')

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 760px;">
    <div class="card-body p-4">
        <form method="POST" novalidate
              action="{{ $costeo->exists ? route('contabilidad.costeos.update', $costeo) : route('contabilidad.costeos.store') }}">
            @csrf
            @if ($costeo->exists) @method('PUT') @endif

            <div class="mb-3">
                <label for="nombre" class="form-label">Alternativa <span class="text-danger">*</span></label>
                <input type="text" id="nombre" name="nombre" maxlength="150" required placeholder="Ej.: Alternativa A: estantes metálicos nuevos"
                       class="form-control @error('nombre') is-invalid @enderror" value="{{ old('nombre', $costeo->nombre) }}">
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="descripcion" class="form-label">Descripción</label>
                <textarea id="descripcion" name="descripcion" rows="2" maxlength="2000"
                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $costeo->descripcion) }}</textarea>
                @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="mb-3">
                <label for="sistema" class="form-label">Sistema de costos <span class="text-danger">*</span></label>
                <select id="sistema" name="sistema" class="form-select @error('sistema') is-invalid @enderror">
                    @foreach ($sistemas as $s)
                        <option value="{{ $s->value }}" @selected(old('sistema', $costeo->sistema?->value ?? 'estandar') === $s->value)>{{ $s->label() }}</option>
                    @endforeach
                </select>
                @error('sistema') <div class="invalid-feedback">{{ $message }}</div> @enderror
                <div class="form-text">Para una tienda que evalúa una inversión se recomienda <strong>estándar</strong>: costos estimados de antemano.</div>
            </div>

            <div class="row g-3">
                <div class="col-sm-4">
                    <label for="volumen_anual" class="form-label">Volumen anual <span class="text-danger">*</span></label>
                    <input type="number" id="volumen_anual" name="volumen_anual" step="0.01" min="0.01" required
                           class="form-control @error('volumen_anual') is-invalid @enderror" value="{{ old('volumen_anual', $costeo->volumen_anual) }}">
                    @error('volumen_anual') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-8">
                    <label for="unidad" class="form-label">Unidad de costeo <span class="text-danger">*</span></label>
                    <input type="text" id="unidad" name="unidad" maxlength="60" required placeholder="Ej.: metro lineal de exhibición, unidad vendida"
                           class="form-control @error('unidad') is-invalid @enderror" value="{{ old('unidad', $costeo->unidad) }}">
                    @error('unidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="form-text mb-3">El costo por unidad = costo total anual ÷ volumen anual. Sirve para comparar alternativas de distinto tamaño.</div>

            <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" id="provisional" name="provisional" value="1" @checked(old('provisional', $costeo->provisional))>
                <label class="form-check-label" for="provisional">Provisional (datos supuestos que se van a reemplazar)</label>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="{{ $costeo->exists ? route('contabilidad.costeos.show', $costeo) : route('contabilidad.costeos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection