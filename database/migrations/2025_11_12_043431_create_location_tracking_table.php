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
        Schema::create('location_tracking', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // Location data
            $table->decimal('latitude', 10, 8);
            $table->decimal('longitude', 11, 8);
            $table->decimal('accuracy', 8, 2)->nullable(); // GPS accuracy in meters
            $table->decimal('altitude', 8, 2)->nullable(); // Optional
            $table->decimal('heading', 5, 2)->nullable(); // Direction in degrees (0-360)
            $table->decimal('speed', 6, 2)->nullable(); // Speed in km/h
            
            // Address info (can be populated async)
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            
            // Tracking metadata
            $table->dateTime('recorded_at')->index();
            $table->foreignId('recorded_by')->constrained('users')->cascadeOnDelete();
            $table->integer('battery_level')->nullable(); // Device battery %
            $table->boolean('is_moving')->default(false)->index();
            
            // Device info (optional)
            $table->string('device_type')->nullable(); // iOS, Android, Web
            $table->string('app_version')->nullable();
            
            $table->timestamp('created_at')->nullable(); // Only created_at, no updated_at
            
            // Composite indexes for performance
            $table->index(['booking_id', 'recorded_at']);
            $table->index(['booking_id', 'is_moving']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_tracking');
    }
};
