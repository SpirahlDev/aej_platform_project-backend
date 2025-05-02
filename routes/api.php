<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectController;


Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware(['web','auth:sanctum']);

Route::post('/login', [AuthController::class,'login'])->middleware('web');



// Route publique pour soumettre un projet
Route::post('/projects', [ProjectController::class, 'store']);

// Routes protégées
Route::middleware('auth:sanctum')->group(function () {
    // Gestion des projets
    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{project}', [ProjectController::class, 'show']);

    // Actions administratives sur les projets
    Route::post('/projects/{project}/review', [ProjectController::class, 'review']);
    Route::post('/projects/{project}/validate', [ProjectController::class, 'validate']);
    Route::post('/projects/{project}/reject', [ProjectController::class, 'reject']);

    // Recommandations IA
    Route::post('/projects/{project}/ai-recommendations', [ProjectController::class, 'generateAiRecommendations']);

    // Export
    Route::get('/projects/export', [ProjectController::class, 'export']);

    // Données de référence pour les formulaires
    Route::get('/project-types', [ProjectController::class, 'getProjectTypes']);
    Route::get('/legal-forms', [ProjectController::class, 'getLegalForms']);
});

// Route pour le téléchargement de documents (avec vérification de token ou d'authentification)
Route::get('/documents/{filePath}', [ProjectController::class, 'downloadDocument'])
    ->middleware('auth:sanctum')
    ->where('filePath', '.*');
