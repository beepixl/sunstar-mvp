<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditDriver extends EditRecord
{
    protected static string $resource = DriverResource::class;

    protected ?string $passwordToUpdate = null;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store password if provided for later use in afterSave
        if (!empty($data['password'])) {
            $this->passwordToUpdate = $data['password'];
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $driver = $this->record;
        
        // Update the associated user's email to match the driver email
        if ($driver->user) {
            $updateData = ['email' => $driver->email];
            
            // Only update password if it was provided
            if ($this->passwordToUpdate !== null) {
                $updateData['password'] = $this->passwordToUpdate;
            }
            
            $driver->user->update($updateData);
        }
    }
}
