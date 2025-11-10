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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
            $table->string('stripe_payment_intent_id')->unique();
            $table->string('stripe_charge_id')->nullable();
            $table->string('application_fee_id')->nullable();
            $table->string('transfer_id')->nullable();
            $table->decimal('amount', 10, 2);
            $table->decimal('commission', 10, 2);
            $table->decimal('net_amount', 10, 2);
            $table->enum('status', ['pending', 'completed', 'failed', 'refunded'])->default('pending');
            $table->timestamp('processed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('stripe_payment_intent_id');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
