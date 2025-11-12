<?php

namespace App\Filament\Resources\Invoices\Schemas;

use App\Models\Client;
use App\Models\Currency;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class InvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Invoice Details')
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label('Invoice Number')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('Auto-generated')
                            ->helperText('Will be generated automatically')
                            ->maxLength(50),
                        
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'draft' => 'Draft',
                                'pending' => 'Pending',
                                'paid' => 'Paid',
                                'overdue' => 'Overdue',
                                'cancelled' => 'Cancelled',
                            ])
                            ->default('pending')
                            ->required()
                            ->searchable(),
                        
                        Select::make('client_id')
                            ->label('Client')
                            ->relationship('client', 'business_name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    $client = Client::find($state);
                                    $currencyCode = $client?->currency_code ?? 'USD';
                                    $set('currency_code', $currencyCode);
                                    
                                    // Also update exchange rate
                                    $currency = Currency::where('code', $currencyCode)->first();
                                    if ($currency) {
                                        $set('exchange_rate', $currency->exchange_rate);
                                    }
                                }
                            }),
                        
                        Select::make('currency_code')
                            ->label('Currency')
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
                            ->live()
                            ->afterStateUpdated(function ($state, callable $set) {
                                if ($state) {
                                    // Update exchange rate when currency changes
                                    $currency = Currency::where('code', $state)->first();
                                    if ($currency) {
                                        $set('exchange_rate', $currency->exchange_rate);
                                    }
                                }
                            })
                            ->helperText(function ($get) {
                                $currencyCode = $get('currency_code');
                                if ($currencyCode && $currencyCode !== 'USD') {
                                    $currency = Currency::where('code', $currencyCode)->first();
                                    return $currency ? 'Exchange Rate: ' . number_format((float)$currency->exchange_rate, 4) . ' ' . $currencyCode . ' = 1 USD' : 'Automatically set from client preference';
                                }
                                return 'Automatically set from client preference';
                            }),
                        
                        Hidden::make('exchange_rate')
                            ->default(1)
                            ->dehydrated(),
                        
                        Select::make('booking_id')
                            ->label('Booking (Optional)')
                            ->relationship('booking', 'booking_number')
                            ->searchable()
                            ->preload()
                            ->placeholder('Select a booking'),
                    ])
                    ->columns(2),
                
                Section::make('Dates')
                    ->schema([
                        DatePicker::make('invoice_date')
                            ->label('Invoice Date')
                            ->default(now())
                            ->required(),
                        
                        DatePicker::make('due_date')
                            ->label('Due Date')
                            ->default(now()->addDays(30))
                            ->required(),
                        
                        DatePicker::make('paid_date')
                            ->label('Paid Date')
                            ->placeholder('Not paid yet'),
                    ])
                    ->columns(3),
                
                Section::make('Amount Details')
                    ->schema([
                        TextInput::make('amount')
                            ->label('Subtotal')
                            ->numeric()
                            ->prefix(function ($get) {
                                $currencyCode = $get('currency_code') ?? 'USD';
                                $currency = Currency::where('code', $currencyCode)->first();
                                return $currency?->symbol ?? '$';
                            })
                            ->required()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $taxAmount = $get('tax_amount') ?? 0;
                                $set('total_amount', $state + $taxAmount);
                            }),
                        
                        TextInput::make('tax_amount')
                            ->label('Tax Amount')
                            ->numeric()
                            ->prefix(function ($get) {
                                $currencyCode = $get('currency_code') ?? 'USD';
                                $currency = Currency::where('code', $currencyCode)->first();
                                return $currency?->symbol ?? '$';
                            })
                            ->required()
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, callable $set, $get) {
                                $amount = $get('amount') ?? 0;
                                $set('total_amount', $amount + $state);
                            }),
                        
                        TextInput::make('total_amount')
                            ->label('Total Amount')
                            ->numeric()
                            ->prefix(function ($get) {
                                $currencyCode = $get('currency_code') ?? 'USD';
                                $currency = Currency::where('code', $currencyCode)->first();
                                return $currency?->symbol ?? '$';
                            })
                            ->required()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(),
                        
                        TextInput::make('amount_paid')
                            ->label('Amount Paid')
                            ->numeric()
                            ->prefix(function ($get) {
                                $currencyCode = $get('currency_code') ?? 'USD';
                                $currency = Currency::where('code', $currencyCode)->first();
                                return $currency?->symbol ?? '$';
                            })
                            ->default(0)
                            ->readonly(),
                    ])
                    ->columns(2),
                
                ]);
    }
}
