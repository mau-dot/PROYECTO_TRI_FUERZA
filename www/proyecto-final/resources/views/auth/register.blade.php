@extends('layouts.app')

@section('title', 'Registro')

@section('content')

<div class="container py-2">
    <div class="row justify-content-center">
        <div class="col-12 col-sm-10 col-md-8 col-lg-6 col-xl-5">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-body p-4 p-sm-5">
                    <h2 class="text-center mb-4 fw-bold text-body">Registro de Usuario</h2>

                    <form method="POST" action="{{ route('register') }}" novalidate>
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

                        <!-- Campo de correo -->
                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email') is-invalid @enderror"
                                value="{{ old('email') }}">
                            @error('email')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Campo de contraseña (en Django era password1) -->
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

                        <!-- Confirmación (en Django era password2) -->
                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password_confirmation" id="password_confirmation"
                                    class="form-control">
                                <span class="input-group-text" style="cursor: pointer;"
                                    onclick="togglePassword('password_confirmation')">
                                    <i class="fa fa-eye" id="icon-password_confirmation"></i>
                                </span>
                            </div>
                        </div>

                        <div class="d-grid mt-3">
                            <button type="submit" class="btn btn-primary btn-lg">
                                Registrarse
                            </button>
                        </div>
                    </form>

                    <p class="text-center mt-4 text-body">
                        ¿Ya tienes cuenta?
                        <a href="{{ route('login') }}" class="link-info fw-semibold">Inicia sesión</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection