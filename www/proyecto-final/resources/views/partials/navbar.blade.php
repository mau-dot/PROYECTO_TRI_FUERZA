<!-- HEADER -->
<header class="sticky-top">
    <nav class="navbar navbar-expand-lg">
        <div class="container">
            <a class="navbar-brand" href="{{ route('home') }}">Sistema Integrador</a>

            <button id="boton-tema" type="button" class="theme-toggle me-2" aria-pressed="false" aria-label="Usar tema oscuro" title="Tema oscuro">
                <i class="fa-solid fa-moon theme-icon" aria-hidden="true"></i>
            </button>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarApp"
                    aria-controls="navbarApp" aria-expanded="false" aria-label="Abrir menú de navegación">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarApp">
                <!-- ENLACES PRINCIPALES (Izquierda) -->
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}" @if (request()->routeIs('home')) aria-current="page" @endif>Inicio</a>
                    </li>
                </ul>

                <!-- USUARIO / INVITADO (Derecha) -->
                <ul class="navbar-nav align-items-lg-center">
                    @auth
                        <li class="nav-item">
                            <a class="nav-link me-3" href="{{ route('perfil') }}" @if (request()->routeIs('perfil')) aria-current="page" @endif>Hola, {{ auth()->user()->username }}</a>
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