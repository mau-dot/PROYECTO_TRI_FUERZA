{{--
    Tarjeta "Evidencias" para cualquier dato. Uso:
        @include('partials.evidencias', ['modelo' => $partida, 'tipo' => 'partida'])
        @include('partials.evidencias', ['modelo' => $empresa, 'tipo' => 'empresa', 'campo' => 'autorizacion', 'titulo' => 'Carta de autorización'])
    'tipo' es el nombre corto del morphMap (AppServiceProvider) y de config/evidencias.php.
--}}
@php
    $campo = $campo ?? '';
    $titulo = $titulo ?? 'Evidencias';
    $vinculadas = $modelo->evidencias()->wherePivot('campo', $campo)->orderBy('evidencias.id')->get();
    $disponibles = \App\Models\Evidencia::query()->whereNotIn('id', $vinculadas->pluck('id'))->latest()->get();
@endphp

<div class="card border-0 shadow-sm rounded-4 mt-3">
    <div class="card-body p-4">
        <div class="d-flex align-items-center gap-2 mb-3">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-paperclip me-2"></i>{{ $titulo }}</h5>
            @include('partials.fuentes', ['n' => $vinculadas->count()])
            <a href="{{ route('evidencias.create', ['vincular' => "$tipo:{$modelo->id}:$campo"]) }}" class="btn btn-sm btn-outline-primary ms-auto">
                <i class="fa-solid fa-upload me-1"></i>Subir nueva
            </a>
        </div>

        @forelse ($vinculadas as $e)
            <div class="d-flex align-items-center gap-2 py-2 border-top">
                <i class="{{ $e->tipo->icono() }} fa-fw text-body-secondary"></i>
                <div class="flex-grow-1">
                    <a href="{{ route('evidencias.show', $e) }}" class="text-decoration-none fw-semibold">{{ $e->titulo }}</a>
                    <div class="small text-body-secondary">
                        {{ $e->tipo->label() }}@if ($e->fuente) · {{ $e->fuente }}@endif @if ($e->fecha) · {{ $e->fecha->format('d/m/Y') }}@endif
                    </div>
                </div>
                @if ($e->archivo_ruta)
                    <a href="{{ route('evidencias.archivo', $e) }}" target="_blank" class="btn btn-sm btn-outline-secondary" title="Abrir archivo"><i class="fa-solid fa-up-right-from-square"></i></a>
                @endif
                <form method="POST" action="{{ route('evidencias.desvincular', $e->pivot->id) }}">
                    @csrf
                    @method('DELETE')
                    <button class="btn btn-sm btn-outline-danger" title="Quitar vínculo (la evidencia no se borra)"
                            onclick="return confirm('¿Quitar esta evidencia de este dato? La evidencia no se borra.')">
                        <i class="fa-solid fa-link-slash"></i>
                    </button>
                </form>
            </div>
        @empty
            <p class="text-body-secondary small mb-2">Todavía no hay evidencias para este dato.</p>
        @endforelse

        @if ($disponibles->isNotEmpty())
            <form method="POST" action="{{ route('evidencias.vincular') }}" class="d-flex gap-2 mt-3">
                @csrf
                <input type="hidden" name="tipo" value="{{ $tipo }}">
                <input type="hidden" name="id" value="{{ $modelo->id }}">
                <input type="hidden" name="campo" value="{{ $campo }}">
                <select name="evidencia_id" class="form-select form-select-sm" required>
                    <option value="">Vincular una evidencia ya subida…</option>
                    @foreach ($disponibles as $d)
                        <option value="{{ $d->id }}">{{ $d->titulo }} ({{ $d->tipo->label() }})</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-primary text-nowrap"><i class="fa-solid fa-link me-1"></i>Vincular</button>
            </form>
        @endif
    </div>
</div>
