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
        Schema::create('booking_photos', function (Blueprint $table) {
            $table->id();
            
            // Relations
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            
            // Photo details
            $table->enum('photo_type', [
                'pickup',
                'delivery',
                'damage',
                'signature',
                'other'
            ])->index();
            $table->string('file_path');
            $table->text('caption')->nullable();
            
            // Metadata
            $table->foreignId('uploaded_by')->constrained('users')->cascadeOnDelete();
            $table->dateTime('uploaded_at');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['booking_id', 'photo_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_photos');
    }
};
