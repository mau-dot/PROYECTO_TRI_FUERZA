{{-- Campos del formulario de un elemento de costo. Se usa al agregar (show) y al editar (elemento). --}}
<div class="row g-3">
    <div class="col-md-5">
        <label for="elemento" class="form-label">Elemento <span class="text-danger">*</span></label>
        <select id="elemento" name="elemento" class="form-select @error('elemento') is-invalid @enderror">
            @foreach (\App\Enums\ElementoCosto::cases() as $op)
                <option value="{{ $op->value }}" @selected(old('elemento', $elemento->elemento?->value) === $op->value)>{{ $op->sigla() }} · {{ $op->label() }}</option>
            @endforeach
        </select>
        @error('elemento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-3">
        <label for="comportamiento" class="form-label">Comportamiento <span class="text-danger">*</span></label>
        <select id="comportamiento" name="comportamiento" class="form-select @error('comportamiento') is-invalid @enderror">
            @foreach (\App\Enums\Comportamiento::cases() as $op)
                <option value="{{ $op->value }}" @selected(old('comportamiento', $elemento->comportamiento?->value) === $op->value)>{{ $op->label() }}</option>
            @endforeach
        </select>
        @error('comportamiento') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-4">
        <label for="cuenta_id" class="form-label">Cuenta del mayor</label>
        <select id="cuenta_id" name="cuenta_id" class="form-select @error('cuenta_id') is-invalid @enderror">
            <option value="">— Ninguna —</option>
            @foreach ($cuentas as $c)
                <option value="{{ $c->id }}" @selected((string) old('cuenta_id', $elemento->cuenta_id) === (string) $c->id)>{{ $c->etiqueta() }}</option>
            @endforeach
        </select>
        @error('cuenta_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
        <label for="concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
        <input type="text" id="concepto" name="concepto" maxlength="150" placeholder="Ej.: Reposición y orden de mercadería"
               class="form-control @error('concepto') is-invalid @enderror" value="{{ old('concepto', $elemento->concepto) }}">
        @error('concepto') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-sm-4">
        <label for="cantidad" class="form-label">Cantidad al año <span class="text-danger">*</span></label>
        <input type="number" id="cantidad" name="cantidad" step="0.01" min="0.01"
               class="form-control @error('cantidad') is-invalid @enderror" value="{{ old('cantidad', $elemento->cantidad) }}">
        @error('cantidad') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-sm-4">
        <label for="unidad_medida" class="form-label">Unidad de medida</label>
        <input type="text" id="unidad_medida" name="unidad_medida" maxlength="30" placeholder="horas, meses, unidades…"
               class="form-control @error('unidad_medida') is-invalid @enderror" value="{{ old('unidad_medida', $elemento->unidad_medida) }}">
        @error('unidad_medida') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-sm-4">
        <label for="costo_unitario" class="form-label">Costo unitario ($) <span class="text-danger">*</span></label>
        <input type="number" id="costo_unitario" name="costo_unitario" step="0.01" min="0"
               class="form-control @error('costo_unitario') is-invalid @enderror" value="{{ old('costo_unitario', $elemento->costo_unitario) }}">
        @error('costo_unitario') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
</div>
<div class="form-check mt-3">
    <input class="form-check-input" type="checkbox" id="estimado" name="estimado" value="1" @checked(old('estimado', $elemento->estimado))>
    <label class="form-check-label" for="estimado">Estimado (supuesto; falta confirmarlo con evidencias)</label>
</div>