<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Http\Requests\ProjectSubmissionRequest;
use App\Models\Project;
use App\Models\ProjectType;
use App\Models\LegalForm;
use App\Services\ProjectManagerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectController
{
    /**
     * Create a new controller instance.
     *
     * @param ProjectManagerService $projectManager
     * @return void
     */
    public function __construct(private ProjectManagerService $projectManager)
    {
    }

    /**
     * Display a listing of the projects.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Récupérer les filtres
        $status = $request->input('status');
        $projectTypeId = $request->input('project_type_id');
        $search = $request->input('search');

        // Construire la requête
        $query = Project::with(['promoter', 'projectType', 'legalForm']);

        // Appliquer les filtres
        if ($status) {
            $query->withStatus($status);
        }

        if ($projectTypeId) {
            $query->ofType($projectTypeId);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('promoter', function ($q) use ($search) {
                    $q->where('email', 'like', "%{$search}%");
                })->orWhere('title', 'like', "%{$search}%");
            });
        }

        // Paginer les résultats
        $projects = $query->orderBy('created_at', 'desc')->paginate(15);

        return ApiResponse::success($projects);
    }

    /**
     * Store a newly created project in storage.
     *
     * @param ProjectSubmissionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(ProjectSubmissionRequest $request)
    {
        try {
            // Données validées
            $data = $request->validated();

            // Fichiers
            $files = [
                'id_card_file' => $request->file('id_card_file'),
                'identity_document' => $request->file('identity_document'),
                'business_plan' => $request->file('business_plan'),
            ];

            $project = $this->projectManager->submitProject($data, $files);

            return ApiResponse::success($project, 'Projet soumis avec succès', 201);
        } catch (\Exception $e) {
            dd($e->getMessage());
            Log::error("Une erreur s'est produite lors d'une soumission :" . $e->getMessage());
            return ApiResponse::fail('Une erreur est survenue lors de la soumission du projet', 500);
        }
    }

//    /**
//     * Display the specified project.
//     *
//     * @param Project $project
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function show(Project $project)
//    {
//        // Charger les relations nécessaires
//        $project->load([
//            'promoter',
//            'projectType',
//            'legalForm',
//            'documents',
//            'validator',
//            'aiRecommendations'
//        ]);
//
//        return ApiResponse::success($project);
//    }
//
//
//    public function review(Project $project)
//    {
//        try {
//            $this->projectManager->reviewProject($project, Auth::id());
//
//            return ApiResponse::success($project, 'Projet mis en cours d\'examen');
//        } catch (\Exception $e) {
//            Log::error("Erreur lors de la mise en examen d'un projet: " . $e->getMessage());
//            return ApiResponse::fail('Une erreur est survenue', 500);
//        }
//    }
//
//
//    public function validate(Project $project)
//    {
//        try {
//            $this->projectManager->validateProject($project, Auth::id());
//
//            return ApiResponse::success($project, 'Projet validé avec succès');
//        } catch (\Exception $e) {
//            Log::error("Erreur lors de la validation d'un projet: " . $e->getMessage());
//            return ApiResponse::fail('Une erreur est survenue', 500);
//        }
//    }
//
//
//    public function reject(Request $request, Project $project)
//    {
//        $validated = $request->validate([
//            'rejection_reason' => 'required|string|max:1000',
//        ]);
//
//        try {
//            $this->projectManager->rejectProject($project, Auth::id(), $validated['rejection_reason']);
//
//            return ApiResponse::success($project, 'Projet rejeté avec succès');
//        } catch (\Exception $e) {
//            Log::error("Erreur lors du rejet d'un projet: " . $e->getMessage());
//            return ApiResponse::fail('Une erreur est survenue', 500);
//        }
//    }
//
//
//    public function downloadDocument($filePath)
//    {
//        if (Storage::exists($filePath)) {
//            return Storage::download($filePath);
//        }
//
//        return ApiResponse::fail('Le fichier demandé n\'existe pas', 404);
//    }
//
//    /**
//     * Export projects to Excel.
//     *
//     * @param Request $request
//     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Illuminate\Http\JsonResponse
//     */
//    public function export(Request $request)
//    {
//        // Récupérer les filtres
//        $filters = [
//            'status' => $request->input('status'),
//            'project_type_id' => $request->input('project_type_id'),
//            'legal_form_id' => $request->input('legal_form_id'),
//            'date_from' => $request->input('date_from'),
//            'date_to' => $request->input('date_to'),
//        ];
//
//        try {
//            // Exporter les projets
//            $filePath = $this->projectManager->exportProjectsToExcel($filters, Auth::id());
//
//            // Générer URL de téléchargement
//            $downloadUrl = url('api/documents/' . $filePath);
//
//            return ApiResponse::success(['download_url' => $downloadUrl], 'Export créé avec succès');
//        } catch (\Exception $e) {
//            Log::error("Erreur lors de l'exportation: " . $e->getMessage());
//            return ApiResponse::fail('Une erreur est survenue lors de l\'exportation', 500);
//        }
//    }
//
//    /**
//     * Generate AI recommendations for a project.
//     *
//     * @param Project $project
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function generateAiRecommendations(Project $project)
//    {
//        try {
//            $recommendation = $this->projectManager->generateAiRecommendations($project);
//
//            return ApiResponse::success($recommendation, 'Recommandations IA générées avec succès');
//        } catch (\Exception $e) {
//            Log::error("Erreur lors de la génération des recommandations IA: " . $e->getMessage());
//            return ApiResponse::fail('Une erreur est survenue', 500);
//        }
//    }
//
//    /**
//     * Get all project types.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function getProjectTypes()
//    {
//        $projectTypes = ProjectType::all();
//        return ApiResponse::success($projectTypes);
//    }
//
//    /**
//     * Get all legal forms.
//     *
//     * @return \Illuminate\Http\JsonResponse
//     */
//    public function getLegalForms()
//    {
//        $legalForms = LegalForm::all();
//        return ApiResponse::success($legalForms);
//    }
}
