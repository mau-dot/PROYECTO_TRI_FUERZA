@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Empresa - Contabilidad')
@section('titulo_modulo', $empresa->exists ? 'Datos de la empresa' : 'Registrar la empresa')

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 900px;">
    <div class="card-body p-4">
        
        <form method="POST" action="{{ route('contabilidad.empresa.update') }}" novalidate>
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label for="nombre" class="form-label">Razón social <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" maxlength="150" required
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $empresa->nombre) }}">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="nombre_comercial" class="form-label">Nombre comercial</label>
                    <input type="text" id="nombre_comercial" name="nombre_comercial" maxlength="150"
                           class="form-control @error('nombre_comercial') is-invalid @enderror"
                           value="{{ old('nombre_comercial', $empresa->nombre_comercial) }}">
                    @error('nombre_comercial') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="nit" class="form-label">NIT</label>
                    <input type="text" id="nit" name="nit" maxlength="20" placeholder="0000-000000-000-0"
                           class="form-control @error('nit') is-invalid @enderror"
                           value="{{ old('nit', $empresa->nit) }}">
                    @error('nit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="nrc" class="form-label">NRC</label>
                    <input type="text" id="nrc" name="nrc" maxlength="20"
                           class="form-control @error('nrc') is-invalid @enderror"
                           value="{{ old('nrc', $empresa->nrc) }}">
                    @error('nrc') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="tasa_iva" class="form-label">Tasa de IVA (%) <span class="text-danger">*</span></label>
                    <input type="number" id="tasa_iva" name="tasa_iva" step="0.01" min="0" max="100" required
                           class="form-control @error('tasa_iva') is-invalid @enderror"
                           value="{{ old('tasa_iva', $empresa->tasa_iva) }}">
                    @error('tasa_iva') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="giro" class="form-label">Giro o actividad económica <span class="text-danger">*</span></label>
                    <input type="text" id="giro" name="giro" maxlength="255" required placeholder="Ej.: Venta al por menor de productos de consumo"
                           class="form-control @error('giro') is-invalid @enderror"
                           value="{{ old('giro', $empresa->giro) }}">
                    @error('giro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="clasificacion" class="form-label">Clasificación <span class="text-danger">*</span></label>
                    <select id="clasificacion" name="clasificacion" required class="form-select @error('clasificacion') is-invalid @enderror">
                        <option value="">Selecciona…</option>
                        @foreach ($clasificaciones as $c)
                            <option value="{{ $c->value }}" @selected(old('clasificacion', $empresa->clasificacion?->value) === $c->value)>{{ $c->label() }}</option>
                        @endforeach
                    </select>
                    @error('clasificacion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-6">
                    <label for="tamano" class="form-label">Tamaño <span class="text-danger">*</span></label>
                    <select id="tamano" name="tamano" required class="form-select @error('tamano') is-invalid @enderror">
                        <option value="">Selecciona…</option>
                        @foreach ($tamanos as $t)
                            <option value="{{ $t->value }}" @selected(old('tamano', $empresa->tamano?->value) === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('tamano') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-8">
                    <label for="direccion" class="form-label">Dirección</label>
                    <input type="text" id="direccion" name="direccion" maxlength="255"
                           class="form-control @error('direccion') is-invalid @enderror"
                           value="{{ old('direccion', $empresa->direccion) }}">
                    @error('direccion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="telefono" class="form-label">Teléfono</label>
                    <input type="text" id="telefono" name="telefono" maxlength="20"
                           class="form-control @error('telefono') is-invalid @enderror"
                           value="{{ old('telefono', $empresa->telefono) }}">
                    @error('telefono') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk me-1"></i>{{ $empresa->exists ? 'Guardar cambios' : 'Registrar empresa' }}
                </button>
            </div>
        </form>
    </div>
</div>

@if ($empresa->exists)
    <div style="max-width: 900px;">
        @include('partials.evidencias', ['modelo' => $empresa, 'tipo' => 'empresa', 'campo' => 'autorizacion', 'titulo' => 'Carta de autorización y confidencialidad'])
        @include('partials.evidencias', ['modelo' => $empresa, 'tipo' => 'empresa', 'campo' => 'estados_financieros', 'titulo' => 'Estados financieros o registros existentes'])
    </div>
@endif

@endsection

