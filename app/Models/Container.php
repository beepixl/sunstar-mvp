<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Client;
use App\Models\Driver;
use App\Models\Location;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Container extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'container_number',
        'container_type',
        'container_size',
        'client_id',
        'driver_id',
        'location_id',
        'status',
        'last_inspection_date',
        'seal_number',
        'reference_number',
        'notes',
        'ocr_data',
    ];

    protected $casts = [
        'ocr_data' => 'array',
        'last_inspection_date' => 'date',
    ];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Locations::class);
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }
}
