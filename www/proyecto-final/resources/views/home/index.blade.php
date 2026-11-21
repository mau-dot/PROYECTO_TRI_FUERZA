@extends('layouts.app')

@section('title', 'Inicio - Sistema Integrador')

@section('content')

@auth
    <div class="container py-4">
        <div class="d-flex justify-content-between align-items-end mb-4 mt-2 gap-3 flex-wrap">
            <div>
                <p class="eyebrow mb-1">Panel de control</p>
                <h1 class="mb-0">Hola, {{ auth()->user()->username }}</h1>
            </div>
            <span class="badge text-bg-secondary">Sesión activa</span>
        </div>

        @if (auth()->user()->is_admin)
            <div class="mb-4">
                {{-- TODO: habilitar este acceso cuando el panel de administración esté implementado. --}}
                <button type="button" class="btn btn-outline-secondary" disabled
                        title="El panel se activará cuando el módulo esté listo">
                    Panel de administración, próximamente
                </button>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-md-6 reveal">
                <div class="card surface-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <p class="eyebrow mb-2">
                            <i class="fa-solid fa-book-open module-icon me-2" aria-hidden="true"></i>Contabilidad
                        </p>
                        <h2 class="h4 card-title">Libro diario y balances</h2>
                        <p class="text-muted flex-grow-1 mt-3">
                            Aquí se registrarán los asientos y se generarán el balance de comprobación y los estados financieros cuando el módulo entre en funcionamiento.
                        </p>
                        <span class="badge text-bg-secondary align-self-start mt-3">Próximamente</span>
                    </div>
                </div>
            </div>

            <div class="col-md-6 reveal">
                <div class="card surface-card h-100">
                    <div class="card-body p-4 d-flex flex-column">
                        <p class="eyebrow mb-2">
                            <i class="fa-solid fa-chart-line module-icon me-2" aria-hidden="true"></i>Economía
                        </p>
                        <h2 class="h4 card-title">Evaluación de proyectos</h2>
                        <p class="text-muted flex-grow-1 mt-3">
                            Esta sección reunirá las proyecciones y los indicadores VAN y TIR para comparar alternativas de inversión con datos reales.
                        </p>
                        <span class="badge text-bg-secondary align-self-start mt-3">Próximamente</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="card surface-card mt-4 reveal">
            <div class="card-body p-4">
                <h2 class="h5 mb-3 section-rule">Resumen general</h2>
                <p class="text-muted mb-0">
                    Todavía no hay registros. Este resumen se completará cuando los módulos estén disponibles.
                </p>
            </div>
        </div>
    </div>
@else
    <div class="container py-4">
        <div class="surface-card p-4 p-md-5 mb-4 text-center reveal">
            <p class="eyebrow mb-2">IEC · SIC · TPI</p>
            <h1 class="display-5 hero-title" data-split>Gestión contable y económica</h1>
            <p class="col-md-8 mx-auto fs-5 text-muted mt-3">
                Un sistema para centralizar los registros contables y la evaluación de proyectos en un mismo lugar, con acceso por usuario y recuperación de contraseña.
            </p>
            <div class="d-flex justify-content-center gap-3 mt-4 flex-wrap">
                <a href="{{ route('register') }}" class="btn btn-primary btn-lg px-4" data-magnet>Crear cuenta</a>
                <a href="{{ route('login') }}" class="btn btn-outline-secondary btn-lg px-4" data-magnet>Iniciar sesión</a>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-md-6 reveal">
                <div class="surface-card h-100 p-4 p-md-5">
                    <h2 class="h3">Precisión contable</h2>
                    <p class="text-muted mt-3">
                        El registro en el libro diario alimenta el balance general y el estado de resultados, sin tener que repetir los datos en cada informe.
                    </p>
                </div>
            </div>
            <div class="col-md-6 reveal">
                <div class="surface-card h-100 p-4 p-md-5">
                    <h2 class="h3">Evaluación estratégica</h2>
                    <p class="text-muted mt-3">
                        Los indicadores VAN y TIR sirven para comparar proyectos de inversión y justificar la decisión con cifras verificables.
                    </p>
                </div>
            </div>
        </div>
    </div>
@endauth

@endsection
