<?php

namespace App\Filament\Resources\Drivers\Schemas;

use App\Models\Driver;
use App\Models\User;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class DriverForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('client_id')
                    ->label('Client')
                    ->relationship('client', 'business_name')
                    ->searchable()
                    ->required(),

                TextInput::make('first_name')->required(),
                TextInput::make('last_name'),

                TextInput::make('mobile')
                    ->tel()
                    ->unique(ignoreRecord: true)
                    ->required(),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $record = $get('../../record');
                                $driverId = $record?->id;
                                $userId = $record?->user_id;
                                
                                // Check if email exists in users table (excluding the associated user)
                                $existsInUsers = User::where('email', $value)
                                    ->when($userId, fn($query) => $query->where('id', '!=', $userId))
                                    ->exists();
                                
                                // Check if email exists in drivers table (excluding current record)
                                $existsInDrivers = Driver::where('email', $value)
                                    ->when($driverId, fn($query) => $query->where('id', '!=', $driverId))
                                    ->exists();
                                
                                // Check if email exists in clients table
                                $existsInClients = \App\Models\Client::where('email', $value)->exists();
                                
                                if ($existsInUsers || $existsInDrivers || $existsInClients) {
                                    $fail('The email has already been taken.');
                                }
                            };
                        },
                    ])
                    ->maxLength(255),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state)) // Only save if not empty
                    ->required(fn ($context) => $context === 'create')
                    ->helperText('Leave blank to keep current password'),

                TextInput::make('license_state')->maxLength(10),
                TextInput::make('license_number')->maxLength(50),
            ])->columns(2);
    }
}
