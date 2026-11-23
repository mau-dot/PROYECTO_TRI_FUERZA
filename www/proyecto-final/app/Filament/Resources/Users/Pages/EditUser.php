<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                // No se puede borrar el usuario con el que estás conectado
                ->hidden(fn (): bool => $this->getRecord()->is(auth()->user())),
        ];
    }

    /**
     * Igual que en CreateUser: forceFill() para poder guardar "is_admin".
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // Seguridad extra: un admin no puede quitarse su propio permiso
        if ($record->is(auth()->user())) {
            unset($data['is_admin']);
        }

        $record->forceFill($data)->save();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}