<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Credit extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'client_id',
        'user_id',
        'credit_type',
        'transaction_type',
        'amount',
        'previous_balance',
        'new_balance',
        'reference_no',
        'notes',
        'status',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'previous_balance' => 'decimal:2',
            'new_balance' => 'decimal:2',
            'approved_at' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    protected static function booted(): void
    {
        // When a credit is deleted, reverse the transaction
        static::deleting(function (Credit $credit) {
            $client = $credit->client;
            
            if ($client) {
                // Reverse the transaction: if it was an 'add', subtract it; if it was a 'deduct', add it back
                if ($credit->transaction_type === 'add') {
                    // Subtract the amount that was previously added
                    $client->available_credit = max($client->available_credit - $credit->amount, 0);
                } else {
                    // Add back the amount that was previously deducted
                    $client->available_credit += $credit->amount;
                }
                
                $client->save();
            }
        });
    }
}
