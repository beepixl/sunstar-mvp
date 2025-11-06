<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('credits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('client_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            $table->enum('credit_type', ['manual', 'refund', 'invoice_adjustment', 'system'])->default('manual');
            $table->enum('transaction_type', ['add', 'deduct'])->default('add');
            $table->decimal('amount', 15, 2);
            $table->decimal('previous_balance', 15, 2)->default(0);
            $table->decimal('new_balance', 15, 2)->default(0);

            $table->string('reference_no')->nullable();
            $table->text('notes')->nullable();

            $table->enum('status', ['pending', 'approved', 'reversed'])->default('approved');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('credits');
    }
};
