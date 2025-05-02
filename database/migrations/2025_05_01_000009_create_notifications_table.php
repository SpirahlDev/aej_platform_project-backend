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
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('notification_type', ['reception', 'approval', 'rejection']);
            $table->enum('channel', ['email', 'sms', 'system'])->default('email');
            $table->string('recipient', 100);
            $table->text('content');
            $table->boolean('is_sent')->default(false);
            $table->timestamp('sent_date')->nullable();
            $table->timestamps();
            
            $table->index(['project_id', 'notification_type'], 'idx_project_type');
            $table->index('sent_date', 'idx_sent_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
