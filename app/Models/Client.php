<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function credits()
{
    return $this->hasMany(Credit::class);
}


public function createdCredits()
{
    return $this->hasMany(Credit::class, 'user_id');
}

    public function approvedCredits()
    {
        return $this->hasMany(Credit::class, 'approved_by');
    }

    public function drivers()
{
    return $this->hasMany(Driver::class);
}


}
