@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', ($partida->exists ? 'Editar' : 'Nueva').' partida - Contabilidad')
@section('titulo_modulo', ($partida->exists ? 'Editar partida #' : 'Nueva partida #').$numero)

@section('modulo')

@if ($grupos->isEmpty())
    <div class="alert alert-warning">
        <i class="fa-solid fa-triangle-exclamation me-2"></i>No hay cuentas de detalle activas.
        <a href="{{ route('contabilidad.cuentas.index') }}">Carga o crea el catálogo de cuentas</a> antes de registrar partidas.
    </div>
@endif

<form method="POST" id="form-partida" novalidate
      action="{{ $partida->exists ? route('contabilidad.partidas.update', $partida) : route('contabilidad.partidas.store') }}">
    @csrf
    @if ($partida->exists) @method('PUT') @endif

    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-body p-4">
            <p class="small text-body-secondary mb-3">Periodo <strong>{{ $periodo->nombre }}</strong> ({{ $periodo->rango() }})</p>
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fecha" class="form-label">Fecha <span class="text-danger">*</span></label>
                    <input type="date" id="fecha" name="fecha" required
                           min="{{ $periodo->fecha_inicio->format('Y-m-d') }}" max="{{ $periodo->fecha_fin->format('Y-m-d') }}"
                           class="form-control @error('fecha') is-invalid @enderror"
                           value="{{ old('fecha', $partida->fecha?->format('Y-m-d')) }}">
                    @error('fecha') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-3">
                    <label for="tipo" class="form-label">Tipo <span class="text-danger">*</span></label>
                    <select id="tipo" name="tipo" class="form-select @error('tipo') is-invalid @enderror">
                        @foreach ($tipos as $t)
                            <option value="{{ $t->value }}" @selected(old('tipo', $partida->tipo?->value) === $t->value)>{{ $t->label() }}</option>
                        @endforeach
                    </select>
                    @error('tipo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6">
                    <label for="concepto" class="form-label">Concepto <span class="text-danger">*</span></label>
                    <input type="text" id="concepto" name="concepto" maxlength="500" required placeholder="Ej.: Venta de mercadería al contado según factura 0045"
                           class="form-control @error('concepto') is-invalid @enderror"
                           value="{{ old('concepto', $partida->concepto) }}">
                    @error('concepto') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>
            </div>
        </div>
    </div>

    @error('lineas')
        <div class="alert alert-danger"><i class="fa-solid fa-scale-unbalanced me-2"></i>{{ $message }}</div>
    @enderror

    <div class="card border-0 shadow-sm rounded-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0" id="tabla-lineas">
                <thead>
                    <tr>
                        <th class="ps-3" style="min-width: 18rem;">Cuenta</th>
                        <th style="min-width: 12rem;">Concepto de la línea</th>
                        <th class="text-end" style="width: 10rem;">Debe</th>
                        <th class="text-end" style="width: 10rem;">Haber</th>
                        <th style="width: 3rem;"></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($lineas as $i => $l)
                        <tr class="linea">
                            <td class="ps-3">
                                <select name="lineas[{{ $i }}][cuenta_id]" class="form-select form-select-sm @error("lineas.$i.cuenta_id") is-invalid @enderror">
                                    <option value="">Selecciona la cuenta…</option>
                                    @foreach ($grupos as $grupo => $cuentas)
                                        <optgroup label="{{ $grupo }}">
                                            @foreach ($cuentas as $c)
                                                <option value="{{ $c->id }}" @selected((string) ($l['cuenta_id'] ?? '') === (string) $c->id)>{{ $c->etiqueta() }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error("lineas.$i.cuenta_id") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </td>
                            <td>
                                <input type="text" name="lineas[{{ $i }}][concepto]" maxlength="255" class="form-control form-control-sm" value="{{ $l['concepto'] ?? '' }}">
                            </td>
                            <td>
                                <input type="number" name="lineas[{{ $i }}][debe]" step="0.01" min="0" inputmode="decimal"
                                       class="form-control form-control-sm text-end monto debe @error("lineas.$i.debe") is-invalid @enderror" value="{{ $l['debe'] ?? '' }}">
                                @error("lineas.$i.debe") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </td>
                            <td>
                                <input type="number" name="lineas[{{ $i }}][haber]" step="0.01" min="0" inputmode="decimal"
                                       class="form-control form-control-sm text-end monto haber @error("lineas.$i.haber") is-invalid @enderror" value="{{ $l['haber'] ?? '' }}">
                                @error("lineas.$i.haber") <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </td>
                            <td class="pe-3 text-end">
                                <button type="button" class="btn btn-sm btn-outline-danger quitar-linea" title="Quitar línea"><i class="fa-solid fa-xmark"></i></button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td class="ps-3" colspan="2">
                            <button type="button" class="btn btn-sm btn-outline-primary" id="agregar-linea"><i class="fa-solid fa-plus me-1"></i>Agregar línea</button>
                        </td>
                        <td class="text-end fw-bold font-monospace" id="total-debe">0.00</td>
                        <td class="text-end fw-bold font-monospace" id="total-haber">0.00</td>
                        <td></td>
                    </tr>
                    <tr>
                        <td colspan="5" class="ps-3 pe-3 text-end">
                            <span id="estado-cuadre" class="badge text-bg-secondary">Sin montos</span>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar partida</button>
        <a href="{{ $partida->exists ? route('contabilidad.partidas.show', $partida) : route('contabilidad.partidas.index') }}" class="btn btn-outline-secondary">Cancelar</a>
    </div>
</form>

@endsection

@push('scripts')
    <script src="{{ asset('js/partida.js') }}"></script>
@endpush