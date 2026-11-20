<!-- HEADER -->
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg navbar-dark bg-body shadow-sm">
        <div class="container">
            <a class="navbar-brand text-body fw-bold" href="{{ route('home') }}">Sistema Integrador</a>

            <button id="boton-tema" class="btn btn-outline-secondary me-2">  🌑/☀️ </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarApp"
                    aria-controls="navbarApp" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarApp">
                <!-- ENLACES PRINCIPALES (Izquierda) -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link text-body" href="{{ route('home') }}">Inicio</a>
                    </li>

                    @auth
                        <li class="nav-item ms-lg-3">
                            {{-- Cambiar "#" por route('contabilidad.dashboard') cuando exista --}}
                            <a class="nav-link text-primary-emphasis fw-semibold" href="#">Contabilidad</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-body" href="#">Economía</a>
                        </li>
                    @endauth
                </ul>

                <!-- USUARIO / INVITADO (Derecha) -->
                <ul class="navbar-nav align-items-lg-center">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link text-success-emphasis me-3" href="{{ route('perfil') }}">Hola, {{ auth()->user()->username }}</a>
                        </li>
                        <li class="nav-item">
                            {{-- En Laravel el logout es POST (protección CSRF), por eso es un formulario --}}
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="nav-link text-danger border-0 bg-transparent">Cerrar sesión</button>
                            </form>
                        </li>
                    @else
                        <li class="nav-item">
                            <a class="nav-link text-body" href="{{ route('login') }}">Iniciar sesión</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger-emphasis fw-semibold" href="{{ route('register') }}">Registrarse</a>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>
</header>