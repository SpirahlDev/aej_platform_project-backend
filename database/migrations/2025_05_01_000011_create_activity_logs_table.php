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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 100);
            $table->string('entity', 50);
            $table->unsignedBigInteger('entity_id')->nullable();
            $table->text('details')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
            
            $table->index('account_id', 'idx_account');
            $table->index(['action', 'entity'], 'idx_action_entity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
