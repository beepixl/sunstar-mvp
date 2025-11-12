<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'name',
        'symbol',
        'exchange_rate',
        'is_active',
        'last_synced_at',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:10',
        'is_active' => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    /**
     * Convert amount from USD to this currency
     */
    public function convertFromUSD(float $usdAmount): float
    {
        return $usdAmount * $this->exchange_rate;
    }

    /**
     * Convert amount from this currency to USD
     */
    public function convertToUSD(float $amount): float
    {
        return $amount / $this->exchange_rate;
    }

    /**
     * Format amount with currency symbol
     */
    public function format(float $amount): string
    {
        return $this->symbol . number_format($amount, 2);
    }

    /**
     * Get popular currencies
     */
    public static function getPopularCurrencies(): array
    {
        return [
            'USD' => 'US Dollar ($)',
            'EUR' => 'Euro (€)',
            'GBP' => 'British Pound (£)',
            'CAD' => 'Canadian Dollar (C$)',
            'AUD' => 'Australian Dollar (A$)',
            'JPY' => 'Japanese Yen (¥)',
            'CNY' => 'Chinese Yuan (¥)',
            'INR' => 'Indian Rupee (₹)',
            'MXN' => 'Mexican Peso ($)',
            'BRL' => 'Brazilian Real (R$)',
        ];
    }
}
