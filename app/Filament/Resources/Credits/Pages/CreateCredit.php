<?php

namespace App\Filament\Resources\Credits\Pages;

use App\Filament\Resources\Credits\CreditResource;
use App\Models\Credit;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCredit extends CreateRecord
{
    protected static string $resource = CreditResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;
        $client = $record->client;

        if ($client) {
            $record->previous_balance = $client->available_credit;
            $newBalance = $client->available_credit;

            if ($record->transaction_type === 'add') {
                $newBalance += $record->amount;
            } else {
                $newBalance -= $record->amount;
            }

            $record->new_balance = max($newBalance, 0);
            $record->approved_by = Auth::id();
            $record->approved_at = now();
            $record->save();

            $client->available_credit = $record->new_balance;
            $client->save();
        }
    }
}
