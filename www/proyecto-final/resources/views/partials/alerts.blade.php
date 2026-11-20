{{-- MENSAJES DEL SISTEMA (equivalente a "messages" de Django)
     Uso en un controlador:  return redirect()->route('home')->with('success', 'Guardado');
     Tipos: success, error, warning, info. "status" lo usa Fortify (p. ej. "enlace enviado"). --}}
@php
    $tipos = ['success' => 'success', 'error' => 'danger', 'warning' => 'warning', 'info' => 'info', 'status' => 'success'];

    // Fortify devuelve códigos en "status" al editar el perfil; aquí se traducen
    $codigosFortify = [
        'profile-information-updated' => 'Tus datos se actualizaron correctamente.',
        'password-updated' => 'Tu contraseña se cambió correctamente.',
    ];
@endphp

@foreach ($tipos as $clave => $color)
    @if (session($clave))
        <div class="container mt-3">
            <div class="alert alert-{{ $color }} alert-dismissible fade show" role="alert">
                {{ $codigosFortify[session($clave)] ?? session($clave) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
            </div>
        </div>
    @endif
@endforeach