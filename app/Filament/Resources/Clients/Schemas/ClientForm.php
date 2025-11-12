<?php

namespace App\Filament\Resources\Clients\Schemas;

use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Checkbox;

class ClientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('First Name')
                    ->required()
                    ->maxLength(255),

                TextInput::make('last_name')
                    ->label('Last Name')
                    ->maxLength(255),

                TextInput::make('business_name')
                    ->label('Business Name')
                    ->maxLength(255),

                TextInput::make('address')
                    ->label('Address')
                    ->maxLength(255),

                TextInput::make('preferred_city')
                    ->label('Preferred City')
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->required()
                    ->rules([
                        function ($get) {
                            return function (string $attribute, $value, \Closure $fail) use ($get) {
                                $record = $get('../../record');
                                $clientId = $record?->id;
                                $userId = $record?->user_id;
                                
                                // Check if email exists in users table (excluding the associated user)
                                $existsInUsers = User::where('email', $value)
                                    ->when($userId, fn($query) => $query->where('id', '!=', $userId))
                                    ->exists();
                                
                                // Check if email exists in clients table (excluding current record)
                                $existsInClients = Client::where('email', $value)
                                    ->when($clientId, fn($query) => $query->where('id', '!=', $clientId))
                                    ->exists();
                                
                                if ($existsInUsers || $existsInClients) {
                                    $fail('The email has already been taken.');
                                }
                            };
                        },
                    ])
                    ->maxLength(255),

                Select::make('currency_code')
                    ->label('Preferred Currency')
                    ->options(function () {
                        return Currency::where('is_active', true)
                            ->orderBy('code')
                            ->get()
                            ->mapWithKeys(fn ($currency) => [
                                $currency->code => $currency->code . ' - ' . $currency->name . ' (' . $currency->symbol . ')'
                            ])
                            ->toArray();
                    })
                    ->default('USD')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->helperText('Currency for invoices and payments'),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->dehydrated(fn ($state) => filled($state)) // Only save if not empty
                    ->required(fn ($context) => $context === 'create'),


                TextInput::make('credit_limit')
                    ->label('Credit Limit')
                    ->numeric()
                    ->default(0),

                TextInput::make('open_balance')
                    ->label('Open Balance')
                    ->numeric()
                    ->default(0)
                    ->readonly(),

                TextInput::make('available_credit')
                    ->label('Available Credit')
                    ->numeric()
                    ->default(0)
                    ->readonly(),

                TextInput::make('total_order_amount')
                    ->label('Total Order Amount')
                    ->numeric()
                    ->default(0)
                    ->readonly(),

                TextInput::make('tax_exempt')
                    ->label('Tax Exempt')
                  ,

                TextInput::make('rewards')
                    ->label('Rewards')
                    ->numeric()
                    ->default(0)
                    ->readonly(),
            ]);
    }
}
