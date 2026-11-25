<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light dark">
    <title>@yield('title', 'Sistema de Evaluación')</title>

    <script>
        (function () {
            var saved = null;
            try { saved = localStorage.getItem('tfu-theme'); } catch (error) {}
            document.documentElement.dataset.bsTheme =
                saved === 'light' || saved === 'dark'
                    ? saved
                    : (window.matchMedia('(prefers-color-scheme: light)').matches ? 'light' : 'dark');
        })();
    </script>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/script.js') }}" defer></script>
    @stack('styles')
</head>
<body class="bg-body text-body d-flex flex-column min-vh-100">

    @include('partials.navbar')

    @section('alerts')
        <div class="container mt-3">
            @include('partials.alerts')
        </div>
    @show

    <!-- CONTENIDO DINÁMICO -->
    <main class="container-fluid py-0 px-0 flex-grow-1">
        @yield('content')
    </main>

    <!-- FOOTER -->
    <footer class="bg-body text-body text-center py-3">
        <div class="container small">
            <p class="mb-0">© {{ date('Y') }} Evaluación Económica y Contable. Todos los derechos reservados.</p>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>