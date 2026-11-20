<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Sistema de Evaluación')</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (iconos del ojo en los campos de contraseña) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="{{ asset('js/script.js') }}"></script>
    @stack('styles')
</head>
<body class="bg-body text-body d-flex flex-column min-vh-100">

    @include('partials.navbar')

    @include('partials.alerts')

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