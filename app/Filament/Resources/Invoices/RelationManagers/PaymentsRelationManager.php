<?php

namespace App\Filament\Resources\Invoices\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payment History';

    public function form(Schema $schema): Schema
    {
        $invoice = $this->getOwnerRecord();
        
        return $schema
            ->components([
                TextInput::make('amount')
                    ->label('Payment Amount')
                    ->numeric()
                    ->required()
                    ->prefix($invoice->getCurrencySymbol())
                    ->maxValue($invoice->getRemainingBalance())
                    ->helperText('Remaining balance: ' . $invoice->formatAmount($invoice->getRemainingBalance()))
                    ->rules([
                        function () use ($invoice) {
                            return function (string $attribute, $value, \Closure $fail) use ($invoice) {
                                $remaining = $invoice->getRemainingBalance();
                                if ((float)$value > $remaining) {
                                    $fail('Payment amount cannot exceed remaining balance of ' . $invoice->formatAmount($remaining));
                                }
                            };
                        },
                    ]),
                
                DatePicker::make('payment_date')
                    ->label('Payment Date')
                    ->default(now())
                    ->required(),
                
                Select::make('payment_method')
                    ->label('Payment Method')
                    ->options([
                        'cash' => 'Cash',
                        'credit_card' => 'Credit Card',
                        'bank_transfer' => 'Bank Transfer',
                        'credit_account' => 'Credit Account',
                        'check' => 'Check',
                    ])
                    ->required()
                    ->searchable(),
                
                TextInput::make('reference_number')
                    ->label('Reference Number')
                    ->placeholder('e.g., Transaction ID, Check Number')
                    ->maxLength(255),
                
                Textarea::make('notes')
                    ->label('Notes')
                    ->rows(3)
                    ->placeholder('Add any payment notes...')
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        $invoice = $this->getOwnerRecord();
        
        return $table
            ->recordTitleAttribute('amount')
            ->columns([
                TextColumn::make('amount')
                    ->label('Amount')
                    ->formatStateUsing(fn ($state) => $invoice->formatAmount((float)$state))
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('payment_date')
                    ->label('Payment Date')
                    ->date()
                    ->sortable(),
                
                TextColumn::make('payment_method')
                    ->label('Method')
                    ->badge()
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state)))
                    ->colors([
                        'success' => 'cash',
                        'info' => 'credit_card',
                        'warning' => 'bank_transfer',
                        'secondary' => fn ($state) => in_array($state, ['credit_account', 'check']),
                    ]),
                
                TextColumn::make('reference_number')
                    ->label('Reference')
                    ->placeholder('N/A')
                    ->searchable(),
                
                TextColumn::make('recordedBy.name')
                    ->label('Recorded By')
                    ->sortable(),
                
                TextColumn::make('created_at')
                    ->label('Recorded At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->disabled(fn () => $invoice->amount_paid >= $invoice->total_amount)
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['recorded_by'] = auth()->id();
                        return $data;
                    })
                    ->before(function (CreateAction $action, RelationManager $livewire) use ($invoice) {
                        // Validate that invoice is not fully paid
                        if ($invoice->amount_paid >= $invoice->total_amount) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Cannot Add Payment')
                                ->body('This invoice is already fully paid.')
                                ->send();
                            
                            $action->halt();
                        }
                    })
                    ->after(function (RelationManager $livewire) {
                        // Send success notification with updated totals
                        $invoice = $livewire->getOwnerRecord();
                        $invoice->refresh(); // Refresh to get latest data
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Payment Recorded')
                            ->body("Total Paid: {$invoice->formatAmount((float)$invoice->amount_paid)} | Balance: {$invoice->formatAmount($invoice->getRemainingBalance())}")
                            ->send();
                        
                        // Dispatch event to refresh the parent invoice form
                        $livewire->dispatch('paymentUpdated');
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn ($record) => $record->invoice->amount_paid >= $record->invoice->total_amount)
                    ->after(function (RelationManager $livewire) {
                        $invoice = $livewire->getOwnerRecord();
                        $invoice->refresh();
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Payment Updated')
                            ->body("Total Paid: {$invoice->formatAmount((float)$invoice->amount_paid)}")
                            ->send();
                        
                        // Dispatch event to refresh the parent invoice form
                        $livewire->dispatch('paymentUpdated');
                    }),
                DeleteAction::make()
                    ->disabled(fn ($record) => $record->invoice->amount_paid >= $record->invoice->total_amount)
                    ->before(function (DeleteAction $action, $record) {
                        // Validate that invoice is not fully paid
                        if ($record->invoice->amount_paid >= $record->invoice->total_amount) {
                            \Filament\Notifications\Notification::make()
                                ->danger()
                                ->title('Cannot Delete Payment')
                                ->body('This invoice is fully paid. Unlock it first by removing a payment.')
                                ->send();
                            
                            $action->halt();
                        }
                    })
                    ->after(function (RelationManager $livewire) {
                        $invoice = $livewire->getOwnerRecord();
                        $invoice->refresh();
                        
                        \Filament\Notifications\Notification::make()
                            ->success()
                            ->title('Payment Deleted')
                            ->body("Total Paid: {$invoice->formatAmount((float)$invoice->amount_paid)} | Balance: {$invoice->formatAmount($invoice->getRemainingBalance())}")
                            ->send();
                        
                        // Dispatch event to refresh the parent invoice form
                        $livewire->dispatch('paymentUpdated');
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                   // DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('payment_date', 'desc');
    }
}
