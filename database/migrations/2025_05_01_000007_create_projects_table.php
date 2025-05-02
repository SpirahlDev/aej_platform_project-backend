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
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('promoter_id')->constrained()->cascadeOnDelete();
            $table->string('title', 200);
            $table->string('summary', 200)->nullable();
            $table->foreignId('legal_form_id')->constrained();
            $table->text('description')->nullable();
            $table->enum('status', ['Submitted', 'Under Review', 'Approved', 'Rejected'])->default('Submitted');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('submission_date')->useCurrent();
            $table->timestamp('review_date')->nullable();
            $table->timestamp('decision_date')->nullable();
            $table->foreignId('validator_account_id')->nullable()->constrained('accounts');
            
            // Référence manuelle à la table 'project_type'
            $table->unsignedBigInteger('project_type_id');
            $table->foreign('project_type_id', 'fk_projects_project_type')
                  ->references('id')
                  ->on('project_type');
                  
            $table->timestamps();
            
            $table->index('status', 'idx_status');
            $table->index('submission_date', 'idx_submission_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
