<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('drivers', function (Blueprint $table): void {
            $table->id();

            // 🔗 Linked to client
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();

            // Basic details
            $table->string('driver_id')->unique(); // internal driver code or ID
            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('mobile')->unique();
            $table->string('license_state', 10)->nullable(); // e.g. GA, TX, CA
            $table->string('license_number')->nullable();

            // Optional system linkage
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('drivers');
    }
};
