@extends('layouts.modulo', ['modulo' => session('modulo', 'contabilidad')])

@section('title', ($evidencia->exists ? 'Editar' : 'Subir').' evidencia')
@section('titulo_modulo', $evidencia->exists ? 'Editar evidencia' : 'Subir evidencia')

@section('modulo')

@if ($destino)
    <div class="alert alert-info"><i class="fa-solid fa-link me-2"></i>Al guardar, quedará vinculada a <strong>{{ $destino }}</strong>.</div>
@endif

<div class="card border-0 shadow-sm rounded-4" style="max-width: 860px;">
    <div class="card-body p-4">
        {{-- enctype multipart/form-data es obligatorio para poder enviar archivos --}}
        <form method="POST" enctype="multipart/form-data" novalidate
              action="{{ $evidencia->exists ? route('evidencias.update', $evidencia) : route('evidencias.store') }}">
            @csrf
            @if ($evidencia->exists) @method('PUT') @endif
            <input type="hidden" name="vincular" value="{{ old('vincular', $vincular) }}">

            <div class="row g-3">
                <div class="col-md-5">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                        @foreach ($tipos as $t)
                            <option value="{{ $t->value }}" @selected(old('tipo', $evidencia->tipo?->value) === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-7">
                    <label for="titulo" class="form-label">Título <span class="text-danger">*</span></label>
                    <input type="text" id="titulo" name="titulo" maxlength="150" placeholder="Ej.: Factura 0045 - Distribuidora El Sol"
                           class="form-control @error('titulo') is-invalid @enderror" value="{{ old('titulo', $evidencia->titulo) }}">
                    @error('titulo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="fecha" class="form-label">Fecha <span class="text-danger solo-entrevista">*</span></label>
                    <input type="date" id="fecha" name="fecha"
                           class="form-control @error('fecha') is-invalid @enderror" value="{{ old('fecha', $evidencia->fecha?->format('Y-m-d')) }}">
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="fuente" class="form-label">
                        <span class="no-entrevista">Fuente (proveedor, institución…)</span>
                        <span class="solo-entrevista">Nombre del entrevistado <span class="text-danger">*</span></span>
                    </label>
                    <input type="text" id="fuente" name="fuente" maxlength="150"
                           class="form-control @error('fuente') is-invalid @enderror" value="{{ old('fuente', $evidencia->fuente) }}">
                    @error('fuente') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4 solo-entrevista">
                    <label for="cargo" class="form-label">Cargo <span class="text-danger">*</span></label>
                    <input type="text" id="cargo" name="cargo" maxlength="100" placeholder="Ej.: Propietario, contador"
                           class="form-control @error('cargo') is-invalid @enderror" value="{{ old('cargo', $evidencia->cargo) }}">
                    @error('cargo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="descripcion" class="form-label">
                        <span class="no-entrevista">Descripción</span>
                        <span class="solo-entrevista">Resumen de la entrevista <span class="text-danger">*</span></span>
                    </label>
                    <textarea id="descripcion" name="descripcion" rows="3" maxlength="5000"
                              class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $evidencia->descripcion) }}</textarea>
                    @error('descripcion') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-12">
                    <label for="archivo" class="form-label">
                        Archivo
                        @unless ($evidencia->exists) <span class="text-danger no-entrevista">*</span> @endunless
                    </label>
                    <input type="file" id="archivo" name="archivo"
                           accept="{{ collect(config('evidencias.extensiones'))->map(fn ($e) => '.'.$e)->implode(',') }}"
                           class="form-control @error('archivo') is-invalid @enderror">
                    @error('archivo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">
                        PDF, imagen, Word o Excel, hasta {{ config('evidencias.tamano_maximo_kb') / 1024 }} MB.
                        @if ($evidencia->archivo_ruta)
                            Archivo actual: <strong>{{ $evidencia->archivo_nombre }}</strong>. Si eliges otro, lo reemplaza.
                        @endif
                        <span class="solo-entrevista">En una entrevista el archivo es opcional (por ejemplo, el audio o la hoja firmada).</span>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="{{ $evidencia->exists ? route('evidencias.show', $evidencia) : url()->previous() }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        // Muestra los campos de entrevista (cargo, resumen obligatorio) solo cuando el tipo es "Entrevista"
        (function () {
            const tipo = document.getElementById('tipo');
            const actualizar = () => {
                const entrevista = tipo.value === 'entrevista';
                document.querySelectorAll('.solo-entrevista').forEach(e => e.classList.toggle('d-none', !entrevista));
                document.querySelectorAll('.no-entrevista').forEach(e => e.classList.toggle('d-none', entrevista));
            };
            tipo.addEventListener('change', actualizar);
            actualizar();
        })();
    </script>
@endpush