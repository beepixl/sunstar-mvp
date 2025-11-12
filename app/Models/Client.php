<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'business_name',
        'address',
        'preferred_city',
        'email',
        'currency_code',
        'password',
        'credit_limit',
        'open_balance',
        'available_credit',
        'total_order_amount',
        'tax_exempt',
        'rewards',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'open_balance' => 'decimal:2',
            'available_credit' => 'decimal:2',
            'total_order_amount' => 'decimal:2',
            'password' => 'hashed',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function credits(): HasMany
    {
        return $this->hasMany(Credit::class);
    }

    public function createdCredits(): HasMany
    {
        return $this->hasMany(Credit::class, 'user_id');
    }

    public function approvedCredits(): HasMany
    {
        return $this->hasMany(Credit::class, 'approved_by');
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'code');
    }

    protected static function booted(): void
    {
        // When a client is deleted, ensure all associated credits are deleted
        static::deleting(function (Client $client) {
            // Force delete credits (bypass soft deletes) when client is deleted
            $client->credits()->withTrashed()->forceDelete();
        });
    }
}
