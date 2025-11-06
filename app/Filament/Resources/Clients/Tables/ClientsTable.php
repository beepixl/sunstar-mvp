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
                TextColumn::make('first_name')->label('First Name')->searchable(),
                TextColumn::make('last_name')->label('Last Name')->searchable(),
                TextColumn::make('business_name')->label('Business Name')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('preferred_city')->label('Preferred City')->searchable(),
                TextColumn::make('credit_limit')->label('Credit Limit')->numeric(),
                TextColumn::make('open_balance')->label('Open Balance')->numeric(),
                TextColumn::make('available_credit')->label('Available Credit')->numeric(),
                TextColumn::make('total_order_amount')->label('Total Order Amount')->numeric(),
                TextColumn::make('tax_exempt')->label('Tax Exempt'),
                TextColumn::make('rewards')->label('Rewards')->numeric(),
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
