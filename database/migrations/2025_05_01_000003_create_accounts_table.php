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
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('person_id');
            $table->foreignId('profile_id')->constrained();
            $table->string('email', 100)->unique('idx_account_email');
            $table->string('password', 255);
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
            
            $table->index('email', 'idx_email');
            $table->index('profile_id', 'idx_profile');
            
            // Référence manuelle à la table 'persons' au lieu de 'people'
            $table->foreign('person_id', 'fk_accounts_persons')
                  ->references('id')
                  ->on('persons')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
