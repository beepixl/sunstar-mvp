<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Container;
use App\Models\StatusHistory;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'client_id',
        'created_by',
        'container_id',
        'driver_id',
        'status',
        'pickup_location_id',
        'delivery_location_id',
        'scheduled_pickup_date',
        'scheduled_delivery_date',
        'container_number_input',
        'pickup_photo',
        'pickup_latitude',
        'pickup_longitude',
        'pickup_address',
        'pickup_notes',
        'picked_up_at',
        'picked_up_by',
        'delivery_photo',
        'delivery_signature',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_address',
        'delivery_notes',
        'delivered_at',
        'delivered_by',
        'tracking_started_at',
        'tracking_ended_at',
        'is_tracking_active',
        'last_known_latitude',
        'last_known_longitude',
        'last_location_update',
        'estimated_distance_km',
        'actual_distance_km',
        'estimated_arrival',
        'recipient_name',
        'recipient_phone',
        'subtotal',
        'tax_amount',
        'total_amount',
        'special_instructions',
        'internal_notes',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_pickup_date' => 'datetime',
            'scheduled_delivery_date' => 'datetime',
            'picked_up_at' => 'datetime',
            'delivered_at' => 'datetime',
            'tracking_started_at' => 'datetime',
            'tracking_ended_at' => 'datetime',
            'last_location_update' => 'datetime',
            'estimated_arrival' => 'datetime',
            'is_tracking_active' => 'boolean',
            'pickup_latitude' => 'decimal:8',
            'pickup_longitude' => 'decimal:8',
            'delivery_latitude' => 'decimal:8',
            'delivery_longitude' => 'decimal:8',
            'last_known_latitude' => 'decimal:8',
            'last_known_longitude' => 'decimal:8',
            'estimated_distance_km' => 'decimal:2',
            'actual_distance_km' => 'decimal:2',
            'subtotal' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Relationships
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(Container::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function pickupLocation(): BelongsTo
    {
        return $this->belongsTo(Locations::class, 'pickup_location_id');
    }

    public function deliveryLocation(): BelongsTo
    {
        return $this->belongsTo(Locations::class, 'delivery_location_id');
    }

    public function pickedUpBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'picked_up_by');
    }

    public function deliveredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delivered_by');
    }

    public function locationTrackings(): HasMany
    {
        return $this->hasMany(LocationTracking::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(BookingPhoto::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class);
    }

    // Model events
    protected static function booted(): void
    {
        // When driver is assigned, change status to 'assigned' (only if status allows)
        static::saving(function (Booking $booking) {
            if ($booking->isDirty('driver_id') && $booking->driver_id !== null) {
                // Only auto-change if current status is pending or assigned
                if (in_array($booking->status, ['pending', 'assigned'])) {
                    $booking->status = 'assigned';
                }
            }
        });

        // After save, update container status and log status changes
        static::saved(function (Booking $booking) {
            // Update container status based on booking status
            if ($booking->container_id && $booking->wasChanged('status')) {
                $containerStatus = match ($booking->status) {
                    // Keep container "assigned" until booking is completed or cancelled
                    'pending', 'assigned', 'picked_up', 'in_transit' => 'assigned',
                    // Only free up when delivered or cancelled
                    'delivered' => 'available', // Free up container when delivered
                    'cancelled' => 'available', // Free up container when cancelled
                    default => 'assigned',
                };
                
                // Use Container::find to ensure we have a fresh instance
                Container::where('id', $booking->container_id)->update(['status' => $containerStatus]);
            }
            
            // If container was just assigned (new booking or container changed)
            if ($booking->wasChanged('container_id') && $booking->container_id) {
                Container::where('id', $booking->container_id)->update(['status' => 'assigned']);
            }
            
            // Log status changes
            if ($booking->wasChanged('status')) {
                StatusHistory::create([
                    'booking_id' => $booking->id,
                    'old_status' => $booking->getOriginal('status') ?? 'pending',
                    'new_status' => $booking->status,
                    'changed_by' => auth()->id() ?? $booking->created_by,
                    'changed_at' => now(),
                ]);
            }
        });
        
        // When booking is deleted, free up the container
        static::deleting(function (Booking $booking) {
            if ($booking->container_id) {
                Container::where('id', $booking->container_id)->update(['status' => 'available']);
            }
        });
    }
}
