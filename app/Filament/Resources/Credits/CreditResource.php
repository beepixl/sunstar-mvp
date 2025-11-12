<?php

declare(strict_types=1);

namespace App\Filament\Resources\Credits;

use App\Models\Credit;
use App\Models\Client;
use App\Models\Currency;
use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Illuminate\Support\Facades\Auth;

final class CreditResource extends Resource
{
    protected static ?string $model = Credit::class;
    protected static ?string $navigationLabel = 'Credits';
    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-banknotes';
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static ?string $recordTitleAttribute = 'reference_no';

    /*
    |--------------------------------------------------------------------------
    | Admin Access Control
    |--------------------------------------------------------------------------
    */
    public static function canViewAny(): bool
    {
        return Auth::user()?->hasRole('Admin') ?? false;
    }

    public static function canAccess(): bool
    {
        return Auth::user()?->hasRole('Admin') ?? false;
    }

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('client_id')
                ->label('Client')
                ->relationship('client', 'business_name')
                ->searchable()
                ->required()
                ->live()
                ->afterStateUpdated(function ($state, callable $set) {
                    if ($state) {
                        $client = Client::find($state);
                        $set('currency_code', $client?->currency_code ?? 'USD');
                    }
                }),

            ToggleButtons::make('transaction_type')
                ->options([
                    'add' => 'Add Credit',
                    'deduct' => 'Deduct Credit',
                ])
                ->inline()
                ->required()
                ->default('add'),

            Select::make('currency_code')
                ->label('Currency')
                ->options(function () {
                    return Currency::where('is_active', true)
                        ->orderBy('code')
                        ->get()
                        ->mapWithKeys(fn ($currency) => [
                            $currency->code => $currency->code . ' - ' . $currency->symbol
                        ])
                        ->toArray();
                })
                ->default('USD')
                ->required()
                ->searchable()
                ->live()
                ->helperText('Amount will be converted to USD for balance tracking'),

            TextInput::make('amount')
                ->label('Amount')
                ->numeric()
                ->required()
                ->live(onBlur: true)
                ->prefix(function ($get) {
                    $currencyCode = $get('currency_code') ?? 'USD';
                    $currency = Currency::where('code', $currencyCode)->first();
                    return $currency?->symbol ?? '$';
                })
                ->helperText(function ($get) {
                    $amount = $get('amount');
                    $currencyCode = $get('currency_code');
                    
                    if (!$amount || !$currencyCode || $currencyCode === 'USD') {
                        return null;
                    }
                    
                    $currency = Currency::where('code', $currencyCode)->first();
                    if (!$currency) {
                        return null;
                    }
                    
                    // Cast to float to avoid number_format errors
                    $usdAmount = (float)$amount / (float)$currency->exchange_rate;
                    return 'Equivalent: $' . number_format($usdAmount, 2) . ' USD (Rate: ' . number_format((float)$currency->exchange_rate, 4) . ')';
                }),

            Select::make('credit_type')
                ->options([
                    'manual' => 'Manual',
                    'refund' => 'Refund',
                    'invoice_adjustment' => 'Invoice Adjustment',
                    'system' => 'System',
                ])
                ->default('manual'),

            TextInput::make('reference_no')->label('Reference No.'),
            Textarea::make('notes')->columnSpanFull(),
            DateTimePicker::make('approved_at')->label('Approved At')->default(now())->hidden(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Table
    |--------------------------------------------------------------------------
    */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->sortable(),
                TextColumn::make('client.business_name')->label('Client'),
                TextColumn::make('transaction_type')->badge()
                    ->colors(['success' => 'add', 'danger' => 'deduct']),
                TextColumn::make('original_amount')
                    ->label('Amount')
                    ->formatStateUsing(function ($record) {
                        $currency = $record->currency;
                        $amount = $record->original_amount ?? $record->amount;
                        $symbol = $currency?->symbol ?? '$';
                        return $symbol . number_format((float)$amount, 2);
                    })
                    ->sortable()
                    ->weight('semibold')
                    ->description(function ($record) {
                        // Show USD equivalent for non-USD credits
                        if ($record->currency_code !== 'USD') {
                            // Use the stored USD amount (already converted)
                            return '$' . number_format((float)$record->amount, 2) . ' USD';
                        }
                        return null;
                    }),
                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->color('info'),
                TextColumn::make('credit_type')->sortable(),
                TextColumn::make('reference_no'),
                TextColumn::make('approvedBy.name')->label('Approved By'),
                BadgeColumn::make('status')->colors([
                    'success' => 'approved',
                    'warning' => 'pending',
                    'danger' => 'reversed',
                ]),
                TextColumn::make('created_at')->dateTime()->label('Date'),
            ])
            ->actions([
                // Edit action removed - credits should not be editable
                DeleteAction::make(),
            ])
            ->bulkActions([
              //  DeleteBulkAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCredits::route('/'),
            'create' => Pages\CreateCredit::route('/create'),
            // Edit page removed - credits should not be editable
        ];
    }
}
