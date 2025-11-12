<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            
            // Booking identification
            $table->string('booking_number')->unique();
            
            // Client and user references
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Container and Driver (ONE container per booking)
            $table->foreignId('container_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            
            // Status
            $table->enum('status', [
                'pending',
                'assigned',
                'picked_up',
                'in_transit',
                'delivered',
                'cancelled'
            ])->default('pending')->index();
            
            // Locations
            $table->foreignId('pickup_location_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->foreignId('delivery_location_id')->nullable()->constrained('locations')->nullOnDelete();
            
            // Scheduling
            $table->dateTime('scheduled_pickup_date')->nullable();
            $table->dateTime('scheduled_delivery_date')->nullable();
            
            // Container input (no verification, just recording)
            $table->string('container_number_input')->nullable();
            
            // Pickup tracking
            $table->string('pickup_photo')->nullable();
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            $table->text('pickup_address')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->dateTime('picked_up_at')->nullable();
            $table->foreignId('picked_up_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Delivery tracking
            $table->string('delivery_photo')->nullable();
            $table->string('delivery_signature')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->text('delivery_address')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->dateTime('delivered_at')->nullable();
            $table->foreignId('delivered_by')->nullable()->constrained('users')->nullOnDelete();
            
            // Live tracking
            $table->dateTime('tracking_started_at')->nullable();
            $table->dateTime('tracking_ended_at')->nullable();
            $table->boolean('is_tracking_active')->default(false)->index();
            $table->decimal('last_known_latitude', 10, 8)->nullable();
            $table->decimal('last_known_longitude', 11, 8)->nullable();
            $table->dateTime('last_location_update')->nullable();
            
            // ETA and distance
            $table->decimal('estimated_distance_km', 8, 2)->nullable();
            $table->decimal('actual_distance_km', 8, 2)->nullable();
            $table->dateTime('estimated_arrival')->nullable();
            
            // Recipient info
            $table->string('recipient_name')->nullable();
            $table->string('recipient_phone')->nullable();
            
            // Pricing
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            
            // Additional information
            $table->text('special_instructions')->nullable();
            $table->text('internal_notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['client_id', 'status']);
            $table->index(['driver_id', 'status']);
            $table->index('scheduled_pickup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
