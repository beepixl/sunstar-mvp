<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InvoicePayment extends Model
{
    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference_number',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    // Relationships
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Model events
    protected static function booted(): void
    {
        // After creating or updating a payment, recalculate invoice totals
        static::created(function (InvoicePayment $payment) {
            $payment->invoice->recalculatePayments();
        });

        static::updated(function (InvoicePayment $payment) {
            $payment->invoice->recalculatePayments();
           
        });

        // After deleting a payment, recalculate invoice totals
        static::deleted(function (InvoicePayment $payment) {
            $payment->invoice->recalculatePayments();
        });
    }
}
