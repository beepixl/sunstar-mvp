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
        Schema::create('status_history', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // Status change details
            $table->string('old_status');
            $table->string('new_status')->index();
            $table->foreignId('changed_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('changed_at')->index();
            $table->text('notes')->nullable();
            
            // Location when status changed
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->text('location_address')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['booking_id', 'changed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_history');
    }
};
