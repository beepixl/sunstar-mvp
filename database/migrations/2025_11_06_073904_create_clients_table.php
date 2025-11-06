<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            $table->string('first_name');
            $table->string('last_name')->nullable();
            $table->string('business_name')->nullable(); // Company / Business Name
            $table->string('address')->nullable();
            $table->string('preferred_city')->nullable();

            $table->string('email')->unique();
            $table->string('password'); // hashed

            $table->decimal('credit_limit', 15, 2)->default(0);
            $table->decimal('open_balance', 15, 2)->default(0);
            $table->decimal('available_credit', 15, 2)->default(0);
            $table->decimal('total_order_amount', 15, 2)->default(0);

            $table->string('tax_exempt')->nullable();
            $table->integer('rewards')->default(0);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
