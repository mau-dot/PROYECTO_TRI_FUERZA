{{-- Formulario para crear y editar periodos --}}
@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', ($periodo->exists ? 'Editar' : 'Nuevo').' periodo - Contabilidad')
@section('titulo_modulo', $periodo->exists ? 'Editar periodo' : 'Nuevo periodo')

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 640px;">
    <div class="card-body p-4">
        <form method="POST" novalidate
              action="{{ $periodo->exists ? route('contabilidad.periodos.update', $periodo) : route('contabilidad.periodos.store') }}">
            @csrf
            @if ($periodo->exists) @method('PUT') @endif

            <div class="mb-3">
                <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                <input type="text" id="nombre" name="nombre" maxlength="100" required placeholder="Ej.: Enero - Junio 2026"
                       class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $periodo->nombre) }}">
                @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <label for="fecha_inicio" class="form-label">Fecha de inicio <span class="text-danger">*</span></label>
                    <input type="date" id="fecha_inicio" name="fecha_inicio" required
                           class="form-control @error('fecha_inicio') is-invalid @enderror"
                           value="{{ old('fecha_inicio', $periodo->fecha_inicio?->format('Y-m-d')) }}">
                    @error('fecha_inicio') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-sm-6">
                    <label for="fecha_fin" class="form-label">Fecha de fin <span class="text-danger">*</span></label>
                    <input type="date" id="fecha_fin" name="fecha_fin" required
                           class="form-control @error('fecha_fin') is-invalid @enderror"
                           value="{{ old('fecha_fin', $periodo->fecha_fin?->format('Y-m-d')) }}">
                    @error('fecha_fin') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="{{ route('contabilidad.periodos.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection