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
        Schema::create('promoters', function (Blueprint $table) {
            $table->id();
            $table->string('id_card_number', 50)->nullable()->unique('idx_id_card_number');
            $table->text('additional_info')->nullable();
            $table->string('last_name', 100);
            $table->string('first_name', 150);
            $table->string('email', 100)->unique('idx_person_email');
            $table->string('phone', 20)->nullable()->unique('idx_phone');
            $table->text('address')->nullable();
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promoters');
    }
};
