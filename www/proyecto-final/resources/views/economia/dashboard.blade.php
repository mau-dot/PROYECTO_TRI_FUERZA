@extends('layouts.modulo', ['modulo' => 'economia'])

@section('title', 'Economía - Sistema Integrador')
@section('titulo_modulo', 'Dashboard de Economía')

@section('modulo')

<div class="alert alert-info">
    <i class="fa-solid fa-circle-info me-2"></i>
    Los cálculos de este módulo usan <strong>solo</strong> los datos verificados del módulo de Contabilidad
    (sistema de costos, cuentas del mayor y evidencias), como exige la guía del proyecto.
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-store me-1"></i>Empresa</div>
                <div class="fw-bold text-truncate">{{ $empresa?->nombre_comercial ?: $empresa?->nombre ?: '—' }}</div>
                <div class="small text-body-secondary">{{ $periodo ? 'Periodo: '.$periodo->nombre : 'Sin periodo contable' }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-code-compare me-1"></i>Alternativas</div>
                <div class="valor">0</div>
                <div class="small text-body-secondary">Disponible en la fase 6</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-percent me-1"></i>TMAR</div>
                <div class="valor text-body-secondary">—</div>
                <div class="small text-body-secondary">Disponible en la fase 6</div>
            </div>
        </div>
    </div>
    <div class="col-md-6 col-xl-3">
        <div class="card h-100 border-0 shadow-sm rounded-4 tarjeta-dato">
            <div class="card-body">
                <div class="text-body-secondary small mb-1"><i class="fa-solid fa-ranking-star me-1"></i>Alternativa recomendada</div>
                <div class="valor text-body-secondary">—</div>
                <div class="small text-body-secondary">Disponible en la fase 7</div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="fw-bold mb-3">Avance del componente económico</h5>
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