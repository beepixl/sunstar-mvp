<?php

namespace App\Filament\Resources\Containers\Tables;

use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\BulkActionGroup;

class ContainersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('container_number')
                    ->sortable()
                    ->searchable(),
                BadgeColumn::make('container_type')
                    ->colors([
                        'primary' => 'dry_van',
                        'success' => 'refrigerated',
                        'warning' => 'open_top',
                        'gray'    => 'flat_rack',
                        'info'    => 'chassis',
                    ]),
                TextColumn::make('container_size')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('Location')
                    ->formatStateUsing(function ($state, $record) {
                        $name = $record->location?->name;
                        $city = $record->location?->city;
                        $country = $record->location?->country;
                        return collect([$name, $city, $country])->filter()->join(', ');
                    }),
                BadgeColumn::make('status')
                    ->colors([
                        'success' => 'available',
                        'warning' => 'assigned',
                        'info'    => 'in_transit',
                        'gray'    => 'delivered',
                        'danger'  => 'damaged',
                        'secondary' => 'maintenance',
                    ]),
                TextColumn::make('last_inspection_date')
                    ->date(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'available'   => 'Available',
                        'assigned'    => 'Assigned',
                        'in_transit'  => 'In Transit',
                        'damaged'     => 'Damaged',
                        'maintenance' => 'Maintenance',
                        'delivered'   => 'Delivered',
                    ]),
                SelectFilter::make('container_type')
                    ->options([
                        'dry_van'      => 'Dry Van',
                        'refrigerated' => 'Refrigerated',
                        'flat_rack'    => 'Flat Rack',
                        'chassis'      => 'Chassis',
                        'open_top'     => 'Open Top',
                    ]),
                SelectFilter::make('location_id')
                    ->relationship('location', 'name'),
            ])
            ->recordActions([
               EditAction::make(), 
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                DeleteBulkAction::make(),
                ]),
            ]);
    }
}
