{{-- Menu lateral de un modulo. Las opciones salen de config/modulos.php --}}
@php
    $empresa = \App\Models\Empresa::actual();
    $periodoActivo = \App\Models\Periodo::activo();
@endphp

<div class="offcanvas-lg offcanvas-start h-100" tabindex="-1" id="sidebarModulo" aria-labelledby="sidebarModuloTitulo">
    <div class="offcanvas-header border-bottom d-lg-none">
        <h5 class="offcanvas-title modulo-sidebar-mobile-title" id="sidebarModuloTitulo">
            <i class="{{ $menu['icono'] }} text-{{ $menu['color'] }} me-2"></i>{{ $menu['titulo'] }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" data-bs-target="#sidebarModulo" aria-label="Cerrar"></button>
    </div>

    <div class="offcanvas-body flex-column w-100 p-0">
        {{-- Encabezado: módulo, empresa y periodo activo --}}
        <div class="modulo-sidebar-head">
            <a href="{{ route($menu['inicio']) }}" class="modulo-sidebar-brand d-none d-lg-flex">
                <span class="modulo-sidebar-brand-icon">
                    <i class="{{ $menu['icono'] }}" aria-hidden="true"></i>
                </span>
                <span>{{ $menu['titulo'] }}</span>
            </a>
            <div class="modulo-sidebar-context">
                <div class="modulo-sidebar-context-row" title="{{ $empresa?->nombre }}">
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                    <span class="modulo-sidebar-context-value text-truncate">
                        {{ $empresa?->nombre_comercial ?: $empresa?->nombre ?: 'Empresa sin registrar' }}
                    </span>
                </div>
                <div class="modulo-sidebar-context-row">
                    <i class="fa-regular fa-calendar" aria-hidden="true"></i>
                    <div class="modulo-sidebar-context-value">
                        @if ($periodoActivo)
                            <a href="{{ route('contabilidad.periodos.index') }}" class="modulo-sidebar-context-link" title="Cambiar de periodo">{{ $periodoActivo->nombre }}</a>
                            <span class="badge text-bg-{{ $periodoActivo->estado->color() }}">{{ $periodoActivo->estado->label() }}</span>
                        @else
                            <a href="{{ route('contabilidad.periodos.index') }}" class="modulo-sidebar-context-link text-warning-emphasis">Sin periodo</a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Opciones --}}
        <nav class="modulo-nav py-2 flex-grow-1">
            @foreach ($menu['secciones'] as $seccion => $opciones)
                <div class="modulo-nav-seccion text-uppercase text-body-secondary px-3 mt-3 mb-1">{{ $seccion }}</div>
                <ul class="nav nav-pills flex-column px-2">
                    @foreach ($opciones as $op)
                        @if (\Illuminate\Support\Facades\Route::has($op['ruta']))
                            @php($activa = request()->routeIs($op['ruta'], $op['activa'] ?? $op['ruta']))
                            <li class="nav-item">
                                <a href="{{ route($op['ruta']) }}"
                                   class="nav-link d-flex align-items-center {{ $activa ? 'active bg-'.$menu['color'] : 'text-body' }}"
                                   @if ($activa) aria-current="page" @endif>
                                    <i class="{{ $op['icono'] }} fa-fw me-2"></i>{{ $op['texto'] }}
                                </a>
                            </li>
                        @else
                            <li class="nav-item">
                                <span class="nav-link d-flex align-items-center text-body-tertiary" title="Disponible en una próxima fase">
                                    <i class="{{ $op['icono'] }} fa-fw me-2"></i>{{ $op['texto'] }}
                                    <span class="badge rounded-pill text-bg-light ms-auto">Pronto</span>
                                </span>
                            </li>
                        @endif
                    @endforeach
                </ul>
            @endforeach
        </nav>

        {{-- Cambiar de módulo --}}
        <div class="modulo-sidebar-footer">
            @if ($menu['inicio'] === 'contabilidad.dashboard')
                <a href="{{ route('economia.dashboard') }}" class="nav-link text-body px-2 py-2"><i class="fa-solid fa-chart-line fa-fw me-2 text-success"></i>Ir a Economía</a>
            @else
                <a href="{{ route('contabilidad.dashboard') }}" class="nav-link text-body px-2 py-2"><i class="fa-solid fa-book fa-fw me-2 text-primary"></i>Ir a Contabilidad</a>
            @endif
            <a href="{{ route('home') }}" class="nav-link text-body px-2 py-2"><i class="fa-solid fa-house fa-fw me-2"></i>Volver al inicio</a>
        </div>
    </div>
</div>