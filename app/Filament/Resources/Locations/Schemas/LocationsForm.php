<?php

namespace App\Filament\Resources\Locations\Schemas;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class LocationsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Location Details')
                    ->schema([
                        TextInput::make('name')
                            ->label('Location Name')
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('code')
                            ->maxLength(50)
                            ->unique(ignoreRecord: true)
                            ->columnSpan(1),

                        Select::make('type')
                            ->options([
                                'depot' => 'Depot',
                                'yard' => 'Yard',
                                'port' => 'Port',
                                'client' => 'Client Site',
                                'warehouse' => 'Warehouse',
                                'city' => 'City',
                            ])
                            ->required()
                            ->columnSpan(1),

                        Select::make('owned_by_client_id')
                            ->label('Owned By Client')
                            ->relationship('client', 'business_name')
                            ->searchable()
                            ->placeholder('Select Client')
                            ->columnSpan(1),

                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Address')
                    ->schema([
                        TextInput::make('address_line1')
                            ->label('Address Line 1')
                            ->columnSpanFull(),

                        TextInput::make('city')
                            ->columnSpan(1),

                        TextInput::make('state')
                            ->columnSpan(1),

                        TextInput::make('country')
                            ->columnSpan(1),

                        TextInput::make('postal_code')
                            ->label('Postal Code')
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Geo Coordinates')
                    ->schema([
                        TextInput::make('latitude')
                            ->numeric()
                            ->columnSpan(1),

                        TextInput::make('longitude')
                            ->numeric()
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Contact Info')
                    ->schema([
                        TextInput::make('contact_person')
                            ->label('Contact Person')
                            ->columnSpan(1),

                        TextInput::make('contact_phone')
                            ->label('Contact Phone')
                            ->tel()
                            ->columnSpan(1),

                        TextInput::make('contact_email')
                            ->label('Contact Email')
                            ->email()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}
