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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained()->onDelete('cascade');
            $table->foreignId('student_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->decimal('price', 10, 2);
            $table->decimal('commission', 10, 2)->default(0);
            $table->text('requirements');
            $table->timestamp('delivery_date')->nullable();
            $table->enum('status', [
                'pending',
                'in_progress',
                'delivered',
                'revision_requested',
                'completed',
                'cancelled',
            ])->default('pending');
            $table->integer('revision_count')->default(0);
            $table->text('cancelled_reason')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index('service_id');
            $table->index('student_id');
            $table->index('client_id');
            $table->index('status');
            $table->index('delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
