@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', ($cuenta->exists ? 'Editar' : 'Nueva').' cuenta - Contabilidad')
@section('titulo_modulo', $cuenta->exists ? 'Editar cuenta' : 'Nueva cuenta')

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 760px;">
    <div class="card-body p-4">
        @if ($padre)
            <p class="text-body-secondary">Subcuenta de <strong>{{ $padre->etiqueta() }}</strong>.</p>
        @endif
        <p class="small text-body-secondary">
            La cuenta padre se asigna sola según el código: <span class="font-monospace">110101</span> queda dentro de
            <span class="font-monospace">1101</span>, que está dentro de <span class="font-monospace">11</span>, y así.
        </p>

        <form method="POST" novalidate
              action="{{ $cuenta->exists ? route('contabilidad.cuentas.update', $cuenta) : route('contabilidad.cuentas.store') }}">
            @csrf
            @if ($cuenta->exists) @method('PUT') @endif

            <div class="row g-3">
                <div class="col-md-4">
                    <label for="codigo" class="form-label">Código <span class="text-danger">*</span></label>
                    <input type="text" id="codigo" name="codigo" inputmode="numeric" maxlength="20" required
                           class="form-control font-monospace @error('codigo') is-invalid @enderror"
                           value="{{ old('codigo', $cuenta->codigo) }}" @readonly($codigoBloqueado)>
                    @error('codigo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    @if ($codigoBloqueado)
                        <div class="form-text">No se puede cambiar: tiene subcuentas o movimientos.</div>
                    @endif
                </div>

                <div class="col-md-8">
                    <label for="nombre" class="form-label">Nombre <span class="text-danger">*</span></label>
                    <input type="text" id="nombre" name="nombre" maxlength="150" required
                           class="form-control @error('nombre') is-invalid @enderror"
                           value="{{ old('nombre', $cuenta->nombre) }}">
                    @error('nombre') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="tipo" name="tipo" required class="form-select @error('tipo') is-invalid @enderror">
                        <option value="">Selecciona…</option>
                        @foreach ($tipos as $t)
                            <option value="{{ $t->value }}" data-naturaleza="{{ $t->naturalezaNormal()->value }}"
                                    @selected(old('tipo', $cuenta->tipo?->value) === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <div class="col-md-4">
                    <label for="naturaleza" class="form-label">Naturaleza <span class="text-danger">*</span></label>
                    <select id="naturaleza" name="naturaleza" required class="form-select @error('naturaleza') is-invalid @enderror">
                        <option value="">Selecciona…</option>
                        @foreach ($naturalezas as $n)
                            <option value="{{ $n->value }}" @selected(old('naturaleza', $cuenta->naturaleza?->value) === $n->value)>{{ $n->label() }}</option>
                        @endforeach
                    </select>
                    @error('naturaleza') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Se llena sola al elegir el tipo. Cámbiala solo en contra cuentas (p. ej. depreciación acumulada).</div>
                </div>

                <div class="col-md-4">
                    <label for="rubro" class="form-label">Rubro</label>
                    <select id="rubro" name="rubro" class="form-select @error('rubro') is-invalid @enderror">
                        <option value="">— (no aplica)</option>
                        @foreach ($rubros as $r)
                            <option value="{{ $r->value }}" @selected(old('rubro', $cuenta->rubro?->value) === $r->value)>{{ $r->label() }}</option>
                        @endforeach
                    </select>
                    @error('rubro') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    <div class="form-text">Solo para activos y pasivos.</div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="{{ route('contabilidad.cuentas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
    <script>
        // Al elegir el tipo, propone la naturaleza normal de ese tipo
        document.getElementById('tipo').addEventListener('change', function () {
            const naturaleza = this.selectedOptions[0]?.dataset.naturaleza;
            if (naturaleza) document.getElementById('naturaleza').value = naturaleza;
        });
    </script>
@endpush