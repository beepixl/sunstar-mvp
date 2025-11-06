<?php

declare(strict_types=1);

namespace App\Filament\Resources\Locations\Tables;

use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables;
use Filament\Tables\Table;

class LocationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\BadgeColumn::make('type')
                    ->colors([
                        'success' => 'depot',
                        'info' => 'yard',
                        'warning' => 'port',
                        'primary' => 'warehouse',
                        'gray' => 'city',
                    ]),
                Tables\Columns\TextColumn::make('city')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('state')->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean(),
                Tables\Columns\TextColumn::make('contact_phone')->toggleable(),
                Tables\Columns\TextColumn::make('country')->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('type')
                    ->options([
                        'depot' => 'Depot',
                        'yard' => 'Yard',
                        'port' => 'Port',
                        'warehouse' => 'Warehouse',
                        'city' => 'City',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')->label('Active Only'),
            ])
            ->actions([
              EditAction::make(),
            ])
            ->bulkActions([
                DeleteBulkAction::make(),
            ]);
    }
}
