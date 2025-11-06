<?php

namespace App\Filament\Resources\Drivers\Pages;

use App\Filament\Resources\Drivers\DriverResource;
use App\Models\Driver;
use App\Models\User;
use Filament\Resources\Pages\CreateRecord;

class CreateDriver extends CreateRecord
{
    protected static string $resource = DriverResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create a corresponding user first
        $user = User::create([
            'name' => trim(($data['first_name'] ?? '') . ' ' . ($data['last_name'] ?? '')),
            'email' => $data['email'],
            'password' => $data['password'], // already hashed via model mutator
        ]);

        // Assign "Driver" role
        $user->assignRole('Driver');

        // Add user_id to the driver data
        $data['user_id'] = $user->id;

        // Auto-generate driver_id if not provided
        if (empty($data['driver_id'])) {
            // Generate driver_id based on client_id and a sequence number
            $clientId = $data['client_id'] ?? 0;
            $driverCount = Driver::where('client_id', $clientId)->count();
            $baseDriverId = 'DRV-' . str_pad($clientId, 4, '0', STR_PAD_LEFT) . '-' . str_pad($driverCount + 1, 3, '0', STR_PAD_LEFT);
            
            // Ensure uniqueness by checking if the driver_id already exists
            $counter = 1;
            $driverId = $baseDriverId;
            while (Driver::where('driver_id', $driverId)->exists()) {
                $driverId = 'DRV-' . str_pad($clientId, 4, '0', STR_PAD_LEFT) . '-' . str_pad($driverCount + 1 + $counter, 3, '0', STR_PAD_LEFT);
                $counter++;
            }
            
            $data['driver_id'] = $driverId;
        }

        return $data;
    }
}
