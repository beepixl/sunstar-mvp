<?php

namespace App\Filament\Resources\Clients\Pages;

use App\Filament\Resources\Clients\ClientResource;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateClient extends CreateRecord
{
    protected static string $resource = ClientResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create a corresponding user first
        $user = User::create([
            'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'password' => $data['password'], // already hashed via model mutator
        ]);

        // Assign "Client" role
        $user->assignRole('Client');

        // Add user_id to the client data
        $data['user_id'] = $user->id;

        return $data;
    }
}
