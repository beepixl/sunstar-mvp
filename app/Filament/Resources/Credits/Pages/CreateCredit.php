<?php

namespace App\Filament\Resources\Credits\Pages;

use App\Filament\Resources\Credits\CreditResource;
use App\Models\Credit;
use App\Models\Currency;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCredit extends CreateRecord
{
    protected static string $resource = CreditResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store original amount in the selected currency
        $data['original_amount'] = $data['amount'];
        
        // Get currency and exchange rate
        $currency = Currency::where('code', $data['currency_code'])->first();
        $data['exchange_rate'] = $currency?->exchange_rate ?? 1;
        
        // Convert amount to USD for balance tracking
        if ($data['currency_code'] !== 'USD' && $currency) {
            $data['amount'] = $data['original_amount'] / $currency->exchange_rate;
        }
        
        $data['user_id'] = Auth::id();
        $data['status'] = 'approved';
        $data['approved_by'] = Auth::id();
        $data['approved_at'] = now();
        
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        $client = $record->client;

        if ($client) {
            $record->previous_balance = $client->available_credit;
            $newBalance = $client->available_credit;

            // Amount is already in USD, so we can directly add/subtract
            if ($record->transaction_type === 'add') {
                $newBalance += $record->amount;
            } else {
                $newBalance -= $record->amount;
            }

            $record->new_balance = max($newBalance, 0);
            $record->save();

            $client->available_credit = $record->new_balance;
            $client->save();
        }
    }
}
