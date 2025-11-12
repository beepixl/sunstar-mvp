<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StatusHistory extends Model
{
    use HasFactory;

    protected $table = 'status_history';

    protected $fillable = [
        'booking_id',
        'old_status',
        'new_status',
        'changed_by',
        'changed_at',
        'notes',
        'location_latitude',
        'location_longitude',
        'location_address',
    ];

    protected function casts(): array
    {
        return [
            'changed_at' => 'datetime',
            'location_latitude' => 'decimal:8',
            'location_longitude' => 'decimal:8',
        ];
    }

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
