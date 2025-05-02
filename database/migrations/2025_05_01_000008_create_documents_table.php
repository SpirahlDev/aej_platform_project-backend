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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', ['ID_Card', 'Identity_Document', 'Business_Plan', 'Validation_PDF', 'Other']);
            $table->string('file_name', 255);
            $table->string('file_path', 255);
            $table->integer('file_size');
            $table->string('mime_type', 100);
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            
            $table->index(['project_id', 'document_type'], 'idx_project_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
