@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', ($alternativa->exists ? 'Editar' : 'Nueva').' alternativa - Economía')

@section('titulo_modulo', $alternativa->exists ? 'Editar alternativa' : 'Nueva alternativa')

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 820px;">
    <div class="card-body p-4">

        <form method="POST"
              novalidate
              action="{{ $alternativa->exists ? route('economia.alternativas.update', $alternativa) : route('economia.alternativas.store') }}">

            @csrf

            @if ($alternativa->exists)
                @method('PUT')
            @endif

            {{-- Nombre --}}
            <div class="mb-3">

                <label for="nombre" class="form-label">
                    Nombre <span class="text-danger">*</span>
                </label>

                <input type="text"
                       id="nombre"
                       name="nombre"
                       maxlength="150"
                       placeholder="Ej.: Alternativa A: estantes metálicos nuevos"
                       class="form-control @error('nombre') is-invalid @enderror"
                       value="{{ old('nombre', $alternativa->nombre) }}">

                @error('nombre')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

            </div>

            {{-- Descripción --}}
            <div class="mb-3">

                <label for="descripcion" class="form-label">
                    Descripción
                </label>

                <textarea id="descripcion"
                          name="descripcion"
                          rows="2"
                          maxlength="3000"
                          class="form-control @error('descripcion') is-invalid @enderror">{{ old('descripcion', $alternativa->descripcion) }}</textarea>

                @error('descripcion')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror

            </div>

            <div class="row g-3">

                {{-- Inversión inicial --}}
                <div class="col-sm-4">

                    <label for="inversion" class="form-label">
                        Inversión inicial ($)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           id="inversion"
                           name="inversion"
                           step="0.01"
                           min="0.01"
                           class="form-control @error('inversion') is-invalid @enderror"
                           value="{{ old('inversion', $alternativa->inversion) }}">

                    @error('inversion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        Precio + instalación + transporte.
                    </div>

                </div>

                {{-- Vida útil --}}
                <div class="col-sm-4">

                    <label for="vida_util" class="form-label">
                        Vida útil (años)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           id="vida_util"
                           name="vida_util"
                           step="1"
                           min="1"
                           max="100"
                           class="form-control @error('vida_util') is-invalid @enderror"
                           value="{{ old('vida_util', $alternativa->vida_util) }}">

                    @error('vida_util')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                {{-- Valor de salvamento --}}
                <div class="col-sm-4">

                    <label for="valor_salvamento" class="form-label">
                        Valor de salvamento ($)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           id="valor_salvamento"
                           name="valor_salvamento"
                           step="0.01"
                           min="0"
                           class="form-control @error('valor_salvamento') is-invalid @enderror"
                           value="{{ old('valor_salvamento', $alternativa->valor_salvamento ?? 0) }}">

                    @error('valor_salvamento')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                </div>

                {{-- Beneficio anual --}}
                <div class="col-sm-4">

                    <label for="beneficio_anual" class="form-label">
                        Beneficio anual ($)
                        <span class="text-danger">*</span>
                    </label>

                    <input type="number"
                           id="beneficio_anual"
                           name="beneficio_anual"
                           step="0.01"
                           min="0"
                           class="form-control @error('beneficio_anual') is-invalid @enderror"
                           value="{{ old('beneficio_anual', $alternativa->beneficio_anual ?? 0) }}">

                    @error('beneficio_anual')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        En <strong>margen</strong> (o ahorro), no en ventas totales.
                        0 si la decisión es solo de costos.
                    </div>

                </div>

                {{-- Costeo de Contabilidad --}}
                <div class="col-sm-8">

                    <label for="costeo_id" class="form-label">
                        Costo de operación: costeo de donde sale
                    </label>

                    <select id="costeo_id"
                            name="costeo_id"
                            class="form-select @error('costeo_id') is-invalid @enderror">

                        <option value="">
                            — Ninguno (escribirlo a mano) —
                        </option>

                        @foreach ($costeos as $c)

                            <option value="{{ $c->id }}"
                                @selected((string) old('costeo_id', $alternativa->costeo_id) === (string) $c->id)>

                                {{ $c->nombre }}
                                · $ {{ \App\Support\Dinero::formato($c->resumen()['total']) }}
                                al año

                            </option>

                        @endforeach

                    </select>

                    @error('costeo_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        Lo recomendado: así el costo queda trazado hasta
                        sus evidencias en el Sistema de costos.
                    </div>

                </div>

                {{-- Costo manual --}}
                <div class="col-sm-4">

                    <label for="costo_operacion" class="form-label">
                        Costo de operación manual ($/año)
                    </label>

                    <input type="number"
                           id="costo_operacion"
                           name="costo_operacion"
                           step="0.01"
                           min="0"
                           class="form-control @error('costo_operacion') is-invalid @enderror"
                           value="{{ old('costo_operacion', $alternativa->costo_operacion) }}">

                    @error('costo_operacion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror

                    <div class="form-text">
                        Solo si no eliges un costeo.
                    </div>

                </div>

            </div>

            {{-- Datos provisionales --}}
            <div class="form-check mt-3">

                <input class="form-check-input"
                       type="checkbox"
                       id="provisional"
                       name="provisional"
                       value="1"
                       @checked(old('provisional', $alternativa->provisional))>

                <label class="form-check-label" for="provisional">
                    Provisional (datos supuestos que se van a reemplazar)
                </label>

            </div>

            {{-- Acciones --}}
            <div class="mt-4 d-flex gap-2">

                <button type="submit" class="btn btn-success">
                    <i class="fa-solid fa-floppy-disk me-1"></i>
                    Guardar
                </button>

                <a href="{{ $alternativa->exists ? route('economia.alternativas.show', $alternativa) : route('economia.alternativas.index') }}"
                   class="btn btn-outline-secondary">
                    Cancelar
                </a>

            </div>

        </form>

    </div>
</div>

@endsection
