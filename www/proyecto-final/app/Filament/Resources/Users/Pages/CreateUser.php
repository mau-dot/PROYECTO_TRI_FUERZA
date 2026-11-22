<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * "is_admin" no está en #[Fillable] del modelo (a propósito, para que
     * nadie se haga admin desde el registro). solo usan admins,
     * se guarda con forceFill().
     */
    protected function handleRecordCreation(array $data): Model
    {
        $record = new ($this->getModel());
        $record->forceFill($data)->save();

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}