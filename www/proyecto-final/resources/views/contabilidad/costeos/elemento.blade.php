@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Editar costo - Sistema de costos')
@section('titulo_modulo', 'Editar costo')

@section('acciones')
    <a href="{{ route('contabilidad.costeos.show', $elemento->costeo) }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i>{{ $elemento->costeo->nombre }}</a>
@endsection

@section('modulo')

<div class="card border-0 shadow-sm rounded-4" style="max-width: 900px;">
    <div class="card-body p-4">
        <form method="POST" action="{{ route('contabilidad.costeos.elementos.update', $elemento) }}" novalidate>
            @csrf
            @method('PUT')
            @include('contabilidad.costeos.campos-elemento')
            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i>Guardar</button>
                <a href="{{ route('contabilidad.costeos.show', $elemento->costeo) }}" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<div style="max-width: 900px;">
    @include('partials.evidencias', ['modelo' => $elemento, 'tipo' => 'costeo_elemento', 'titulo' => 'Evidencias de este costo'])
</div>

@endsection