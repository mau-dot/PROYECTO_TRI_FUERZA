<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;

/**
 * Formulario para crear y editar usuarios desde /admin.
 * Usa las mismas reglas que el registro de Fortify (CreateNewUser).
 */
class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Datos del usuario')
                    ->columns(2)
                    ->schema([
                        TextInput::make('username')
                            ->label('Usuario')
                            ->required()
                            ->maxLength(150)
                            ->alphaDash()
                            // Igual que el login: el usuario se guarda en minúsculas
                            ->dehydrateStateUsing(fn (string $state): string => mb_strtolower($state))
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Ese nombre de usuario ya está en uso.',
                                'alpha_dash' => 'El usuario solo puede tener letras, números, guiones y guiones bajos.',
                            ]),

                        TextInput::make('email')
                            ->label('Correo electrónico')
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->validationMessages([
                                'unique' => 'Este correo electrónico ya está registrado en el sistema.',
                            ]),
                    ]),

                Section::make('Contraseña')
                    ->description(fn (string $operation): ?string => $operation === 'edit'
                        ? 'Déjala en blanco para no cambiarla.'
                        : null)
                    ->columns(2)
                    ->schema([
                        TextInput::make('password')
                            ->label('Contraseña')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->confirmed()
                            // Obligatoria solo al crear
                            ->required(fn (string $operation): bool => $operation === 'create')
                            // Si se deja vacía al editar, no se toca la contraseña actual.
                            // No hace falta Hash::make(): el modelo la encripta (cast "hashed").
                            ->dehydrated(fn (?string $state): bool => filled($state)),

                        TextInput::make('password_confirmation')
                            ->label('Confirmar contraseña')
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false),
                    ]),

                Section::make('Permisos')
                    ->schema([
                        Toggle::make('is_admin')
                            ->label('Administrador')
                            ->helperText('Puede entrar a este panel (/admin).')
                            // Nadie puede quitarse a sí mismo el permiso de admin
                            ->disabled(fn (?Model $record): bool => $record?->is(auth()->user()) ?? false),
                    ]),
            ]);
    }
}