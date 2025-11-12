<?php

namespace App\Filament\Resources\Bookings\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class BookingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('booking_number')
                    ->label('Booking #')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                    
                TextColumn::make('client.business_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('container.container_number')
                    ->label('Container')
                    ->searchable()
                    ->sortable(),
                    
                TextColumn::make('driver.driver_id')
                    ->label('Driver')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not assigned'),
                    
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'gray' => 'pending',
                        'warning' => 'assigned',
                        'info' => 'picked_up',
                        'primary' => 'in_transit',
                        'success' => 'delivered',
                        'danger' => 'cancelled',
                    ]),
                    
                TextColumn::make('pickupLocation.name')
                    ->label('Pickup')
                    ->searchable()
                    ->limit(30),
                    
                TextColumn::make('deliveryLocation.name')
                    ->label('Delivery')
                    ->searchable()
                    ->limit(30),
                    
                TextColumn::make('scheduled_pickup_date')
                    ->label('Pickup Date')
                    ->dateTime('M d, Y')
                    ->sortable(),
                    
                IconColumn::make('is_tracking_active')
                    ->label('Live')
                    ->boolean()
                    ->trueIcon('heroicon-o-signal')
                    ->falseIcon('heroicon-o-signal-slash')
                    ->trueColor('success')
                    ->falseColor('gray'),
                    
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('USD')
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('M d, Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'assigned' => 'Assigned',
                        'picked_up' => 'Picked Up',
                        'in_transit' => 'In Transit',
                        'delivered' => 'Delivered',
                        'cancelled' => 'Cancelled',
                    ]),
                    
                SelectFilter::make('client')
                    ->relationship('client', 'business_name')
                    ->searchable()
                    ->preload(),
                    
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
