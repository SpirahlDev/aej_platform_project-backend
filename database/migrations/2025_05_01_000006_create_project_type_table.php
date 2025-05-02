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
        Schema::create('project_type', function (Blueprint $table) {
            $table->id();
            $table->string('name', 45);
            $table->string('code', 45)->unique('idx_project_type_code');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->softDeletes('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_type');
    }
};
