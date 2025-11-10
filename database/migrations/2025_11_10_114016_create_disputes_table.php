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
        Schema::create('disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->unique()->constrained()->onDelete('cascade');
            $table->foreignId('opened_by_id')->constrained('users')->onDelete('cascade');
            $table->text('reason');
            $table->enum('status', ['open', 'resolved', 'refunded', 'released'])->default('open');
            $table->text('resolution_notes')->nullable();
            $table->foreignId('resolved_by_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index('order_id');
            $table->index('status');
            $table->index('opened_by_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('disputes');
    }
};
