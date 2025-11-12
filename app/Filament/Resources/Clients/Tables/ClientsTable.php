<?php

namespace App\Filament\Resources\Clients\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ClientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('business_name')
                    ->label('Business Name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('first_name')
                    ->label('First Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('last_name')
                    ->label('Last Name')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->copyable()
                    ->icon('heroicon-o-envelope'),
                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->color('info')
                    ->sortable()
                    ->searchable(),
                TextColumn::make('credit_limit')
                    ->label('Credit Limit')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('available_credit')
                    ->label('Credit Available')
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('credit_used')
                    ->label('Credit Used')
                    ->state(fn ($record) => $record->credit_limit - $record->available_credit)
                    ->formatStateUsing(fn ($state) => '$' . number_format($state, 2))
                    ->sortable()
                    ->toggleable()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'success'),
                TextColumn::make('preferred_city')
                    ->label('City')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tax_exempt')
                    ->label('Tax Exempt')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('rewards')
                    ->label('Rewards')
                    ->numeric()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
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
