@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-sm-5">
                    <h2 class="text-center mb-4 fw-bold text-body">Iniciar sesión</h2>

                    <form method="POST" action="{{ route('login') }}" novalidate>
                        @csrf

                        <!-- Campo de usuario -->
                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" name="username" id="username"
                                class="form-control @error('username') is-invalid @enderror"
                                value="{{ old('username') }}" autofocus>
                            @error('username')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo de contraseña -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror">
                                <span class="input-group-text" style="cursor: pointer;"
                                    onclick="togglePassword('password')">
                                    <i class="fa fa-eye" id="icon-password"></i>
                                </span>
                            </div>
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Ingresar
                            </button>
                        </div>
                    </form>

                    <div class="mt-3">
                        <a href="{{ route('password.request') }}" class="text-decoration-none">¿Olvidaste tu contraseña?</a>
                    </div>

                    <p class="text-center mt-4 text-body">
                        ¿No tienes cuenta?
                        <a href="{{ route('register') }}" class="link-info fw-semibold">Regístrate</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection