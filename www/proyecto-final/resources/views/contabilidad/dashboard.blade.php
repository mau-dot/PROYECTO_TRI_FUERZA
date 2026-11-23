@extends('layouts.modulo', ['modulo' => 'contabilidad'])

@section('title', 'Contabilidad - Sistema Integrador')
@section('titulo_modulo', 'Dashboard de Contabilidad')

@section('modulo')

@unless ($empresa)
    <div class="alert alert-warning d-flex align-items-center justify-content-between flex-wrap gap-2">
        <span><i class="fa-solid fa-triangle-exclamation me-2"></i>Todavía no está registrada la empresa del caso. Es el primer paso.</span>
        <a href="{{ route('contabilidad.empresa.edit') }}" class="btn btn-warning btn-sm">Registrar empresa</a>
    </div>
@endunless

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-store me-1"></i>Empresa</div>
                <div class="fw-bold text-truncate">{{ $empresa?->nombre_comercial ?: $empresa?->nombre ?: '—' }}</div>
                <div class="small text-body-secondary">{{ $empresa?->clasificacion->label() ?? 'Sin clasificar' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-regular fa-calendar me-1"></i>Periodo activo</div>
                @if ($periodo)
                    <div class="fw-bold">{{ $periodo->nombre }}
                        <span class="badge text-bg-{{ $periodo->estado->color() }} ms-1">{{ $periodo->estado->label() }}</span>
                    </div>
                    <div class="small text-body-secondary">{{ $periodo->rango() }}</div>
                @else
                    <div class="fw-bold">—</div>
                    <a href="{{ route('contabilidad.periodos.create') }}" class="small">Crear periodo</a>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-book-open me-1"></i>Partidas del periodo</div>
                <div class="valor">0</div>
                <div class="small text-body-secondary">Disponible en la fase 2</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-scale-balanced me-1"></i>Balanza de comprobación</div>
                <div class="valor text-body-secondary">—</div>
                <div class="small text-body-secondary">Disponible en la fase 4</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Avance del componente contable</h5>
        <ul class="list-group list-group-flush">
            @foreach ($avance as $paso)
                <li class="list-group-item d-flex align-items-center bg-transparent px-0">
                    @if ($paso['hecho'])
                        <i class="fa-solid fa-circle-check text-success fa-fw me-2"></i>
                    @else
                        <i class="fa-regular fa-circle text-body-tertiary fa-fw me-2"></i>
                    @endif
                    <span class="{{ $paso['hecho'] ? '' : 'text-body-secondary' }}">{{ $paso['texto'] }}</span>
                    <span class="badge rounded-pill text-bg-light ms-auto">Fase {{ $paso['fase'] }}</span>
                </li>
            @endforeach
        </ul>
    </div>
</div>

@endsection