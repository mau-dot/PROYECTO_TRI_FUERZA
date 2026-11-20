@extends('layouts.app')

@section('title', 'Recuperar contraseña')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">

            @if (session('status'))
                {{-- Reemplaza a password_reset_sent.html de Django --}}
                <div class="alert alert-success text-center shadow-sm" role="alert">
                    <h4 class="alert-heading">¡Correo enviado!</h4>
                    <p class="mt-3">Hemos enviado las instrucciones para restablecer tu contraseña a tu correo.</p>
                    <hr>
                    <p class="mb-0 small">Revisa tu bandeja de entrada (en desarrollo: <a href="http://localhost:8025" target="_blank">Mailpit</a>).</p>
                </div>
            @else
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="card-title text-center mb-3">Recuperar Contraseña</h3>
                        <p class="text-muted text-center text-sm">Ingresa tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>

                        <form method="POST" action="{{ route('password.email') }}" novalidate>
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <input type="email" name="email" id="email"
                                    class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email') }}" autofocus>
                                @error('email')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary">Enviar enlace</button>
                            </div>
                        </form>

                        <div class="text-center mt-3">
                            <a href="{{ route('login') }}" class="text-decoration-none">Volver a iniciar sesión</a>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</div>
@endsection