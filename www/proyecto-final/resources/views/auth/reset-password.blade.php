@extends('layouts.app')

@section('title', 'Nueva contraseña')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-4">
                    <h3 class="card-title text-center mb-3">Nueva Contraseña</h3>
                    <p class="text-muted text-center text-sm">Por favor, ingresa tu nueva contraseña a continuación.</p>

                    {{-- Si el enlace es inválido o caducó, el error aparece en "email" --}}
                    @error('email')
                        <div class="alert alert-danger">
                            {{ $message }}
                            <div class="mt-2">
                                <a href="{{ route('password.request') }}" class="btn btn-sm btn-primary">Solicitar nuevo enlace</a>
                            </div>
                        </div>
                    @enderror

                    <form method="POST" action="{{ route('password.update') }}" novalidate>
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <input type="hidden" name="email" value="{{ old('email', $request->email) }}">

                        <div class="mb-3">
                            <label for="password" class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="password"
                                    class="form-control @error('password') is-invalid @enderror" autofocus>
                                <span class="input-group-text" style="cursor: pointer;"
                                    onclick="togglePassword('password')">
                                    <i class="fa fa-eye" id="icon-password"></i>
                                </span>
                            </div>
                            @error('password')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar contraseña</label>
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control">
                        </div>

                        <div class="d-grid mt-4">
                            <button type="submit" class="btn btn-success">Guardar contraseña</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection