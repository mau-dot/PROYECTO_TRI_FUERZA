@extends('layouts.app')

@section('title', 'Perfil')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8 col-xl-7">

            <h2 class="text-body mb-1">Bienvenido, {{ auth()->user()->username }}</h2>
            <p class="text-body-secondary mb-4">Email: {{ auth()->user()->email }}</p>

            {{-- ============ 1. EDITAR DATOS ============ --}}
            {{-- Los errores de este formulario llegan en la "bolsa" updateProfileInformation --}}
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-3">Mis datos</h4>

                    <form method="POST" action="{{ route('user-profile-information.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="username" class="form-label">Usuario</label>
                            <input type="text" name="username" id="username"
                                class="form-control @error('username', 'updateProfileInformation') is-invalid @enderror"
                                value="{{ old('username', auth()->user()->username) }}">
                            @error('username', 'updateProfileInformation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Correo electrónico</label>
                            <input type="email" name="email" id="email"
                                class="form-control @error('email', 'updateProfileInformation') is-invalid @enderror"
                                value="{{ old('email', auth()->user()->email) }}">
                            @error('email', 'updateProfileInformation')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </form>
                </div>
            </div>

            {{-- ============ 2. CAMBIAR CONTRASEÑA ============ --}}
            {{-- Los errores de este formulario llegan en la "bolsa" updatePassword --}}
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-body p-4">
                    <h4 class="card-title mb-3">Cambiar contraseña</h4>

                    <form method="POST" action="{{ route('user-password.update') }}" novalidate>
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="current_password" class="form-label">Contraseña actual</label>
                            <div class="input-group">
                                <input type="password" name="current_password" id="current_password"
                                    class="form-control @error('current_password', 'updatePassword') is-invalid @enderror">
                                <span class="input-group-text" style="cursor: pointer;"
                                    onclick="togglePassword('current_password')">
                                    <i class="fa fa-eye" id="icon-current_password"></i>
                                </span>
                            </div>
                            @error('current_password', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password" class="form-label">Nueva contraseña</label>
                            <div class="input-group">
                                <input type="password" name="password" id="new_password"
                                    class="form-control @error('password', 'updatePassword') is-invalid @enderror">
                                <span class="input-group-text" style="cursor: pointer;"
                                    onclick="togglePassword('new_password')">
                                    <i class="fa fa-eye" id="icon-new_password"></i>
                                </span>
                            </div>
                            @error('password', 'updatePassword')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="new_password_confirmation" class="form-label">Confirmar nueva contraseña</label>
                            <input type="password" name="password_confirmation" id="new_password_confirmation"
                                class="form-control">
                        </div>

                        <button type="submit" class="btn btn-success">Cambiar contraseña</button>
                    </form>
                </div>
            </div>

            {{-- ============ 3. CERRAR SESIÓN ============ --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-danger">Cerrar sesión</button>
            </form>

        </div>
    </div>
</div>
@endsection