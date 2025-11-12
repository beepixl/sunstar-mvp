<?php

namespace App\Filament\Resources\Invoices\Tables;
use Filament\Tables\Enums\FiltersLayout;

use App\Mail\InvoiceMail;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Mail;

class InvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('invoice_number')
                    ->label('Invoice ID')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                
                TextColumn::make('client.business_name')
                    ->label('Client')
                    ->searchable()
                    ->sortable()
                    ->url(fn ($record) => $record->client ? route('filament.admin.resources.clients.edit', $record->client) : null),
                
                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->formatStateUsing(fn ($record) => $record->formatAmount((float)$record->total_amount))
                    ->sortable()
                    ->weight('semibold')
                    ->description(function ($record) {
                        // Show USD equivalent for non-USD invoices
                        if ($record->currency_code !== 'USD') {
                            $exchangeRate = (float)$record->exchange_rate;
                            if ($exchangeRate <= 1.0) {
                                $currency = $record->currency;
                                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
                            }
                            $usdAmount = $exchangeRate > 0 ? (float)$record->total_amount / $exchangeRate : 0;
                            return '$' . number_format($usdAmount, 2) . ' USD';
                        }
                        return null;
                    }),
                
                TextColumn::make('currency_code')
                    ->label('Currency')
                    ->badge()
                    ->color('info')
                    ->sortable(),
                
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->colors([
                        'secondary' => 'draft',
                        'warning' => 'pending',
                        'success' => 'paid',
                        'danger' => 'overdue',
                        'gray' => 'cancelled',
                    ])
                    ->sortable()
                    ->searchable(),
                
                TextColumn::make('due_date')
                    ->label('Due Date')
                    ->date()
                    ->sortable(),
                
                // TextColumn::make('booking.booking_number')
                //     ->label('Booking')
                //     ->searchable()
                //     ->sortable()
                //     ->toggleable()
                //     ->placeholder('N/A')
                //     ->url(fn ($record) => $record->booking ? route('filament.admin.resources.bookings.edit', $record->booking) : null),
                
                TextColumn::make('amount_paid')
                    ->label('Amount Paid')
                    ->formatStateUsing(fn ($record) => $record->formatAmount((float)$record->amount_paid))
                    ->sortable()
                    ->weight('medium')
                    ->color(fn ($record) => $record->amount_paid >= $record->total_amount ? 'success' : 'warning')
                    ->description(function ($record) {
                        // Show USD equivalent for non-USD invoices
                        if ($record->currency_code !== 'USD' && $record->amount_paid > 0) {
                            $exchangeRate = (float)$record->exchange_rate;
                            if ($exchangeRate <= 1.0) {
                                $currency = $record->currency;
                                $exchangeRate = $currency ? (float)$currency->exchange_rate : 1.0;
                            }
                            $usdAmount = $exchangeRate > 0 ? (float)$record->amount_paid / $exchangeRate : 0;
                            return '$' . number_format($usdAmount, 2) . ' USD';
                        }
                        return null;
                    }),
                
                TextColumn::make('payment_method')
                    ->label('Payment Method')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->formatStateUsing(fn ($state) => $state ? ucwords(str_replace('_', ' ', $state)) : 'N/A'),
                
                TextColumn::make('invoice_date')
                    ->label('Invoice Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                
                TextColumn::make('paid_date')
                    ->label('Paid Date')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('Not paid'),
                
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'pending' => 'Pending',
                        'paid' => 'Paid',
                        'overdue' => 'Overdue',
                        'cancelled' => 'Cancelled',
                    ])
                    ->multiple(),
                
                SelectFilter::make('client')
                    ->relationship('client', 'business_name')
                    ->searchable()
                    ->preload(),
                
               //TrashedFilter::make(),
                    ],layout: FiltersLayout::AboveContent)
            ->recordActions([
                Action::make('download_pdf')
                    ->label('Download PDF')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->color('success')
                    ->action(function ($record) {
                        return $record->downloadPdf();
                    }),
                
                Action::make('send_email')
                    ->label('Send Email')
                    ->icon('heroicon-o-envelope')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Send Invoice Email')
                    ->modalDescription('This will send the invoice PDF to the client\'s email address.')
                    ->action(function ($record) {
                        try {
                            Mail::to($record->client->email)->send(new InvoiceMail($record));
                            
                            Notification::make()
                                ->success()
                                ->title('Email Sent!')
                                ->body('Invoice has been sent to ' . $record->client->email)
                                ->send();
                        } catch (\Exception $e) {
                            Notification::make()
                                ->danger()
                                ->title('Failed to send email')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
                
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
