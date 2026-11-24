@extends('layouts.modulo', ['modulo' => session('modulo', 'contabilidad')])

@section('title', $evidencia->titulo.' - Evidencias')
@section('titulo_modulo', $evidencia->titulo)

@section('acciones')
    <a href="{{ route('evidencias.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>Evidencias</a>
    <a href="{{ route('evidencias.edit', $evidencia) }}" class="btn btn-primary"><i class="fa-solid fa-pen me-1"></i>Editar</a>
@endsection

@section('modulo')

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <p class="mb-3"><i class="{{ $evidencia->tipo->icono() }} me-2"></i><strong>{{ $evidencia->tipo->label() }}</strong></p>
                <dl class="row mb-0 small">
                    @if ($evidencia->fecha)
                        <dt class="col-5 text-body-secondary">Fecha</dt><dd class="col-7">{{ $evidencia->fecha->format('d/m/Y') }}</dd>
                    @endif
                    @if ($evidencia->fuente)
                        <dt class="col-5 text-body-secondary">{{ $evidencia->tipo->value === 'entrevista' ? 'Entrevistado' : 'Fuente' }}</dt><dd class="col-7">{{ $evidencia->fuente }}</dd>
                    @endif
                    @if ($evidencia->cargo)
                        <dt class="col-5 text-body-secondary">Cargo</dt><dd class="col-7">{{ $evidencia->cargo }}</dd>
                    @endif
                    <dt class="col-5 text-body-secondary">Subida por</dt><dd class="col-7">{{ $evidencia->usuario?->username ?? '—' }} · {{ $evidencia->created_at->format('d/m/Y') }}</dd>
                    @if ($evidencia->archivo_ruta)
                        <dt class="col-5 text-body-secondary">Archivo</dt><dd class="col-7 text-break">{{ $evidencia->archivo_nombre }} ({{ $evidencia->tamanoLegible() }})</dd>
                    @endif
                </dl>
                @if ($evidencia->descripcion)
                    <hr>
                    <p class="mb-0" style="white-space: pre-line;">{{ $evidencia->descripcion }}</p>
                @endif

                @if ($evidencia->archivo_ruta)
                    <div class="d-flex gap-2 mt-3">
                        <a href="{{ route('evidencias.archivo', $evidencia) }}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-up-right-from-square me-1"></i>Abrir</a>
                        <a href="{{ route('evidencias.archivo', [$evidencia, 'descargar' => 1]) }}" class="btn btn-sm btn-outline-secondary"><i class="fa-solid fa-download me-1"></i>Descargar</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-7">
        <div class="card border-0 shadow-sm rounded-4 h-100">
            <div class="card-body p-4">
                <h5 class="fw-bold mb-3">Datos que respalda</h5>
                @forelse ($evidencia->vinculos as $v)
                    <div class="d-flex align-items-center gap-2 py-2 border-top">
                        <i class="fa-solid fa-link text-body-secondary"></i>
                        <div class="flex-grow-1">
                            @if ($url = $v->urlDato())
                                <a href="{{ $url }}" class="text-decoration-none">{{ $v->descripcionDato() }}</a>
                            @else
                                {{ $v->descripcionDato() }}
                            @endif
                        </div>
                        <form method="POST" action="{{ route('evidencias.desvincular', $v) }}">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-outline-danger" title="Quitar vínculo"><i class="fa-solid fa-link-slash"></i></button>
                        </form>
                    </div>
                @empty
                    <p class="text-body-secondary small">
                        Todavía no respalda ningún dato. Para vincularla, abre el dato (por ejemplo, una partida del libro diario)
                        y usa <strong>Vincular</strong> en su tarjeta de evidencias.
                    </p>
                @endforelse
            </div>
        </div>
    </div>
</div>

@if ($evidencia->tieneArchivo() && $evidencia->seVeEnNavegador())
    <div class="card border-0 shadow-sm rounded-4 mt-3">
        <div class="card-body p-2">
            @if ($evidencia->esImagen())
                <img src="{{ route('evidencias.archivo', $evidencia) }}" alt="{{ $evidencia->titulo }}" class="img-fluid rounded-3 d-block mx-auto" style="max-height: 70vh;">
            @else
                <iframe src="{{ route('evidencias.archivo', $evidencia) }}" title="{{ $evidencia->titulo }}" class="w-100 rounded-3 border-0" style="height: 70vh;"></iframe>
            @endif
        </div>
    </div>
@endif

<form method="POST" action="{{ route('evidencias.destroy', $evidencia) }}" class="mt-3">
    @csrf
    @method('DELETE')
    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar la evidencia y su archivo? Se quitará de todos los datos que respalda.')">
        <i class="fa-solid fa-trash me-1"></i>Eliminar evidencia
    </button>
</form>

@endsection