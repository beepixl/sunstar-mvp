<?php

namespace App\Filament\Resources\Bookings\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BookingForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Booking Information')
                    ->schema([
                        TextInput::make('booking_number')
                            ->label('Booking Number')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->default(fn () => 'BK-' . date('Y') . '-' . str_pad(
                                (string) (
                                    \App\Models\Booking::withTrashed()->max('id') + 1
                                ),
                                5,
                                '0',
                                STR_PAD_LEFT
                            ))
                            ->disabled()
                            ->dehydrated()
                            ->columnSpan(1),
                            
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'Pending',
                                'assigned' => 'Assigned',
                                'picked_up' => 'Picked Up',
                                'in_transit' => 'In Transit',
                                'delivered' => 'Delivered',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Section::make('Client & Container')
                    ->schema([
                        Select::make('client_id')
                            ->label('Client')
                            ->relationship('client', 'business_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live() // Makes it reactive
                            ->afterStateUpdated(fn ($state, callable $set) => $set('driver_id', null)) // Reset driver when client changes
                            ->columnSpan(1),
                            
                        Select::make('container_id')
                            ->label('Container')
                            ->relationship(
                                name: 'container',
                                titleAttribute: 'container_number',
                                modifyQueryUsing: fn ($query, $get) => $query->where(function ($q) use ($get) {
                                    $q->where('status', 'available')
                                      ->when($get('../../record'), fn ($query, $record) => $query->orWhere('id', $record->container_id));
                                })
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->helperText('Only available containers are shown')
                            ->columnSpan(1),
                            
                        Select::make('driver_id')
                            ->label('Assign Driver')
                            ->relationship(
                                name: 'driver',
                                titleAttribute: 'driver_id',
                                modifyQueryUsing: fn ($query, $get) => $query->where('client_id', $get('client_id'))
                            )
                            ->getOptionLabelFromRecordUsing(fn ($record) => $record->driver_id . ' - ' . $record->full_name)
                            ->searchable(['driver_id', 'first_name', 'last_name'])
                            ->preload()
                            ->disabled(fn ($get) => !$get('client_id'))
                            ->columnSpan(2)
                            ->helperText('Select a client first. When driver is assigned, status will automatically change to "Assigned"'),
                    ])
                    ->columns(2),
                    
                Section::make('Locations')
                    ->schema([
                        Select::make('pickup_location_id')
                            ->label('Pickup Location')
                            ->relationship('pickupLocation', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                            
                        Select::make('delivery_location_id')
                            ->label('Delivery Location')
                            ->relationship('deliveryLocation', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Section::make('Schedule')
                    ->schema([
                        DateTimePicker::make('scheduled_pickup_date')
                            ->label('Scheduled Pickup Date')
                            ->required()
                            ->columnSpan(1),
                            
                        DateTimePicker::make('scheduled_delivery_date')
                            ->label('Scheduled Delivery Date')
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(2),
                    
                Section::make('Pricing')
                    ->schema([
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->columnSpan(1),
                            
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->columnSpan(1),
                            
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix('$')
                            ->default(0)
                            ->required()
                            ->columnSpan(1),
                    ])
                    ->columns(3),
                    
                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('special_instructions')
                            ->label('Special Instructions')
                            ->rows(3)
                            ->columnSpanFull(),
                            
                        Textarea::make('internal_notes')
                            ->label('Internal Notes')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->collapsible(),
            ]);
    }
}
