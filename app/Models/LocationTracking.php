<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LocationTracking extends Model
{
    use HasFactory;

    protected $table = 'location_tracking';

    // Only use created_at, not updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'booking_id',
        'latitude',
        'longitude',
        'accuracy',
        'altitude',
        'heading',
        'speed',
        'address',
        'city',
        'state',
        'country',
        'recorded_at',
        'recorded_by',
        'battery_level',
        'is_moving',
        'device_type',
        'app_version',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:8',
            'longitude' => 'decimal:8',
            'accuracy' => 'decimal:2',
            'altitude' => 'decimal:2',
            'heading' => 'decimal:2',
            'speed' => 'decimal:2',
            'recorded_at' => 'datetime',
            'is_moving' => 'boolean',
            'battery_level' => 'integer',
        ];
    }

    // Relationships
    public function booking(): BelongsTo
    {
        return $this->belongsTo(Booking::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
