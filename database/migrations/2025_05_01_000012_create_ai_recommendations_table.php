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
        Schema::create('ai_recommendations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->decimal('viability_score', 5, 2)->nullable();
            $table->decimal('innovation_score', 5, 2)->nullable();
            $table->decimal('market_score', 5, 2)->nullable();
            $table->text('recommendations');
            $table->timestamps();
            
            $table->index('project_id', 'idx_project');
            $table->index(['viability_score', 'innovation_score', 'market_score'], 'idx_scores');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_recommendations');
    }
};
