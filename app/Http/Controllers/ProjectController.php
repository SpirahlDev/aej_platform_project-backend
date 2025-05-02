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

    public function validateProject(Request $request){
        try{
            $data = $request->validated();
            
        }catch(\Exception $e){

        }
    }

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
