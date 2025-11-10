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
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 100)->unique();
            $table->text('value');
            $table->enum('type', ['string', 'integer', 'decimal', 'boolean', 'json'])->default('string');
            $table->timestamps();

            $table->index('key');
        });

        // Insert default settings
        DB::table('settings')->insert([
            [
                'key'        => 'commission_rate',
                'value'      => '0.15',
                'type'       => 'decimal',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'order_timeout_hours',
                'value'      => '48',
                'type'       => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'auto_approve_days',
                'value'      => '5',
                'type'       => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'max_revisions',
                'value'      => '2',
                'type'       => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'max_portfolio_size_mb',
                'value'      => '10',
                'type'       => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'key'        => 'max_attachment_size_mb',
                'value'      => '25',
                'type'       => 'integer',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
