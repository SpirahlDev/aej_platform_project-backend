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
        Schema::create('exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id')->constrained();
            $table->enum('export_type', ['PDF', 'XLSX']);
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->text('filter_parameters')->nullable();
            $table->timestamps();
            
            $table->index(['account_id', 'export_type'], 'idx_account_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exports');
    }
};
