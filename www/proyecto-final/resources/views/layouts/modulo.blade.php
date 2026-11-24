{{--
    Layout de los modulos (Contabilidad y Economía): sidebar fijo + contenido.
    Uso en una vista:
        @extends('layouts.modulo', ['modulo' => 'contabilidad'])   o  'economia'
        @section('title', 'Titulo de la pestaña')
        @section('titulo_modulo', 'Titulo grande de la pagina')
        @section('modulo') ...contenido... @endsection
--}}
@extends('layouts.app')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/modulo.css') }}">
@endpush

@section('content')
    @php($menu = config("modulos.$modulo"))

    <div class="modulo d-flex">
        {{-- SIDEBAR: fijo en pantallas grandes, panel deslizable (offcanvas) en celular --}}
        <aside class="modulo-sidebar bg-body-tertiary">
            @include('partials.sidebar', ['menu' => $menu])
        </aside>

        {{-- CONTENIDO --}}
        <section class="modulo-contenido flex-grow-1 p-3 p-lg-4">
            <div class="d-flex align-items-center gap-2 mb-4">
                <button class="btn btn-outline-{{ $menu['color'] }} d-lg-none" type="button"
                        data-bs-toggle="offcanvas" data-bs-target="#sidebarModulo" aria-controls="sidebarModulo">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <h3 class="fw-bold mb-0">@yield('titulo_modulo')</h3>
                <div class="ms-auto">@yield('acciones')</div>
            </div>

            @yield('modulo')
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        // El sidebar queda pegado justo debajo del navbar, sea cual sea su alto
        (function () {
            const ajustar = () => document.documentElement.style.setProperty(
                '--alto-navbar', (document.querySelector('header.sticky-top')?.offsetHeight ?? 0) + 'px');
            ajustar();
            window.addEventListener('resize', ajustar);
        })();
    </script>
@endpush