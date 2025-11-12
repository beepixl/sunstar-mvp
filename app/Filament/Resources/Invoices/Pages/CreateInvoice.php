<?php

namespace App\Filament\Resources\Invoices\Pages;

use App\Filament\Resources\Invoices\InvoiceResource;
use App\Models\Currency;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        
        // Store exchange rate at time of invoice creation
        $currency = Currency::where('code', $data['currency_code'] ?? 'USD')->first();
        $data['exchange_rate'] = $currency?->exchange_rate ?? 1;
        
        return $data;
    }
}
