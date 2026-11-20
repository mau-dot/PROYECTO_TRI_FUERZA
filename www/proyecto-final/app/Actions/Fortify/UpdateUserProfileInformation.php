<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

/**
 * Editar usuario y correo desde el perfil (CAMBIO: 'name' -> 'username').
 */
class UpdateUserProfileInformation implements UpdatesUserProfileInformation
{
    /**
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function update(User $user, array $input): void
    {
        // Igual que en el login y el registro: el usuario se guarda en minusculas
        $input['username'] = Str::lower($input['username'] ?? '');

        Validator::make($input, [
            'username' => [
                'required',
                'string',
                'max:150',
                'alpha_dash',
                Rule::unique('users')->ignore($user->id),
            ],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($user->id),
            ],
        ], [
            'username.unique' => 'Ese nombre de usuario ya está en uso.',
            'username.alpha_dash' => 'El usuario solo puede tener letras, números, guiones y guiones bajos.',
            'email.unique' => 'Este correo electrónico ya está registrado en el sistema.',
        ])->validateWithBag('updateProfileInformation');

        $user->forceFill([
            'username' => $input['username'],
            'email' => $input['email'],
        ])->save();
    }
}