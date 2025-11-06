<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('containers', function (Blueprint $table): void {
            $table->id();

            // Unique Container ID
            $table->string('container_number')->unique();

            // Type and Size
            $table->enum('container_type', [
                'dry_van',
                'open_top',
                'flat_rack',
                'double_door',
                'refrigerated',
                'chassis',
            ])->nullable()->index();

            $table->enum('container_size', [
                '20ft',
                '20hc',
                '40ft',
                '40hc',
                '45ft',
                '45hcpw',
            ])->nullable()->index();

            // Ownership and links
            $table->foreignId('client_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('driver_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('location_id')->nullable()->constrained()->nullOnDelete();

            // Operational Status
            $table->enum('status', [
                'available',
                'assigned',
                'in_transit',
                'delivered',
                'damaged',
                'maintenance',
                'retired',
            ])->default('available')->index();

            // Additional Details
            $table->date('last_inspection_date')->nullable();
            $table->string('seal_number')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();

            // Optional OCR Data (JSON)
            $table->json('ocr_data')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('containers');
    }
};
