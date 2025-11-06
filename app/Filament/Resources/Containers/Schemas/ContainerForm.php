<?php

namespace App\Filament\Resources\Containers\Schemas;

use Filament\Schemas\Schema;
use App\Models\Container;
use App\Models\Driver;
use App\Models\Location;
use App\Models\Client;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;

class ContainerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
               TextInput::make('container_number')
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),

           Select::make('container_type')
                ->options([
                    'dry_van' => 'Dry Van',
                    'open_top' => 'Open Top',
                    'flat_rack' => 'Flat Rack',
                    'double_door' => 'Double Door',
                    'refrigerated' => 'Refrigerated',
                    'chassis' => 'Chassis',
                ])
                ->required(),

           Select::make('container_size')
                ->options([
                    '20ft' => '20FT',
                    '20hc' => '20FT High Cube',
                    '40ft' => '40FT',
                    '40hc' => '40FT High Cube',
                    '45ft' => '45FT',
                    '45hcpw' => '45FT HC Pallet Wide',
                ])
                ->required(),

           Select::make('client_id')
                ->relationship('client', 'business_name')
                ->searchable()
                ->preload(),

           Select::make('driver_id')
                ->relationship('driver', 'first_name')
                ->searchable()
                ->preload(),

           Select::make('location_id')
                ->relationship('location', 'name')
                ->searchable()
                ->preload()
                ->label('Current Location'),

           Select::make('status')
                ->options([
                    'available' => 'Available',
                    'assigned' => 'Assigned',
                    'in_transit' => 'In Transit',
                    'delivered' => 'Delivered',
                    'damaged' => 'Damaged',
                    'maintenance' => 'Maintenance',
                    'retired' => 'Retired',
                ])
                ->default('available'),

           DatePicker::make('last_inspection_date')
                ->label('Last Inspection'),

           TextInput::make('seal_number')->maxLength(255),
           TextInput::make('reference_number')->maxLength(255),
           Textarea::make('notes')->rows(2),
            ]);
    }
}
