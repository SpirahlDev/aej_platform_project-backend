<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Promoter;
use App\Models\Document;
use App\Models\Notification;
use App\Models\AiRecommendation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Mail;
//use Barryvdh\DomPDF\Facade\Pdf;
//use Maatwebsite\Excel\Facades\Excel;
//use App\Exports\ProjectsExport;
use App\Mail\ProjectSubmissionConfirmation;
use App\Mail\ProjectValidationNotification;
use App\Mail\ProjectRejectionNotification;
use Exception;
use Carbon\Carbon;
use Illuminate\Support\Str;

class ProjectManagerService
{
    /**
     * Submit a new project
     *
     * @param array $data Project and promoter data
     * @param array $files Array of uploaded files
     * @return Project
     * @throws Exception
     */
    public function submitProject(array $data, array $files): Project
    {
        DB::beginTransaction();

        try {
            // Create or update Promoter with direct attributes
            $promoter = $this->createOrUpdatePromoter($data);

            // Create Project
            $project = new Project([
                'promoter_id' => $promoter->id,
                'title' => $data['title'],
                'summary' => $data['summary'],
                'description' => $data['description'] ?? null,
                'project_type_id' => $data['project_type_id'],
                'legal_form_id' => $data['legal_form_id'],
                'status' => 'Submitted',
                'submission_date' => now(),
            ]);

            $project->save();

            // Store documents
            $this->storeProjectDocuments($promoter,$project, $files);

            // Send confirmation notification
//            $this->sendSubmissionConfirmation($project);

            DB::commit();

            return $project;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Review a project
     *
     * @param Project $project
     * @param int $reviewerAccountId
     * @return Project
     */
    public function reviewProject(Project $project, int $reviewerAccountId): Project
    {
        $project->status = 'Under Review';
        $project->review_date = now();
        $project->save();

        // Log the review activity
        $this->logActivity($reviewerAccountId, 'review', 'projects', $project->id);

        return $project;
    }

    /**
     * Validate a project
     *
     * @param Project $project
     * @param int $validatorAccountId
     * @return Project
     */
    public function validateProject(Project $project, int $validatorAccountId): Project
    {
        $project->status = 'Approved';
        $project->decision_date = now();
        $project->validator_account_id = $validatorAccountId;
        $project->save();

        // Generate PDF validation document
        $this->generateValidationPDF($project);

        // Send notification to promoter
//        $this->sendValidationNotification($project);

        // Log the validation activity
        $this->logActivity($validatorAccountId, 'validate', 'projects', $project->id);

        return $project;
    }

    /**
     * Reject a project
     *
     * @param Project $project
     * @param int $validatorAccountId
     * @param string $rejectionReason
     * @return Project
     */
    public function rejectProject(Project $project, int $validatorAccountId, string $rejectionReason): Project
    {
        $project->status = 'Rejected';
        $project->decision_date = now();
        $project->validator_account_id = $validatorAccountId;
        $project->rejection_reason = $rejectionReason;
        $project->save();

        // Send notification to promoter
        $this->sendRejectionNotification($project);

        // Log the rejection activity
        $this->logActivity($validatorAccountId, 'reject', 'projects', $project->id);

        return $project;
    }

    /**
     * Export projects to Excel
     *
     * @param array $filters
     * @param int $accountId
     * @return string Path to the exported file
     */
    public function exportProjectsToExcel(array $filters, int $accountId): string
    {
        $fileName = 'projects_export_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        $filePath = 'exports/' . $fileName;

        Excel::store(new ProjectsExport($filters), $filePath);

        // Create export record
        $export = new \App\Models\Export([
            'account_id' => $accountId,
            'export_type' => 'XLSX',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'filter_parameters' => $filters,
        ]);

        $export->save();

        return $filePath;
    }

    /**
     * Generate AI recommendations for a project
     *
     * @param Project $project
     * @return AiRecommendation
     */
    public function generateAiRecommendations(Project $project): AiRecommendation
    {
        // Here you would integrate with a third-party AI service
        // For now, we'll create a placeholder recommendation

        $recommendation = new AiRecommendation([
            'project_id' => $project->id,
            'viability_score' => rand(1, 5),
            'innovation_score' => rand(1, 5),
            'market_score' => rand(1, 5),
            'recommendations' => 'This is a placeholder recommendation. Integrate with an AI service for actual market analysis.',
        ]);

        $recommendation->save();

        return $recommendation;
    }

    /**
     * Create or update a promoter record
     *
     * @param array $data
     * @return Promoter
     */
    private function createOrUpdatePromoter(array $data): Promoter
    {

        $promoter = Promoter::where('email', $data['email'])
            ->orWhere('phone', $data['phone'])
            ->orWhere('id_card_number',$data['id_card_number'])
            ->first();

        if (!$promoter) {
            $promoter = new Promoter([
                'id_card_number' => $data['id_card_number'],
                'birth_date' => $data['birth_date'],
                'last_name' => $data['last_name'],
                'first_name' => $data['first_name'],
                'birth_place' => $data['birth_place'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'address' => $data['address'] ?? null,
                'additional_info' => $data['additional_info'] ?? null,
            ]);

            $promoter->save();
        }

        return $promoter;
    }

    /**
     * Store project documents
     *
     * @param Project $project
     * @param array $files
     * @return void
     */
    private function storeProjectDocuments(Promoter $promoter,Project $project, array $files): void
    {
        $promoter_dirname='documents/projects/'.$promoter->getKey().'-'.Str::slug($promoter->last_name.'-'.$promoter->first_name);

        $project_dir=Str::slug($project->title.'-'.$project->getKey());

        $file_base_dir=$promoter_dirname.'/'.$project_dir;

        $documentTypes = [
            'id_card_file' => 'ID_Card',
            'identity_document' => 'Identity_Document',
            'business_plan' => 'Business_Plan',
        ];

        foreach ($documentTypes as $fileKey => $documentType) {
            if (isset($files[$fileKey])) {
                $file = $files[$fileKey];
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs($file_base_dir.'/'.$fileKey, $fileName);

                $document = new Document([
                    'project_id' => $project->id,
                    'document_type' => $documentType,
                    'file_name' => $fileName,
                    'file_path' => $filePath,
                    'file_size' => $file->getSize(),
                    'mime_type' => $file->getMimeType(),
                ]);

                $document->save();
            }
        }
    }

    /**
     * Generate validation PDF for an approved project
     *
     * @param Project $project
     * @return Document
     */
    private function generateValidationPDF(Project $project): Document
    {
        $pdf = PDF::loadView('pdf.project_validation', [
            'project' => $project,
            'promoter' => $project->promoter,
            'generationDate' => now()->format('d/m/Y'),
        ]);

        $fileName = 'validation_project_' . $project->id . '.pdf';
        $filePath = 'documents/validations/' . $fileName;

        Storage::put($filePath, $pdf->output());

        $document = new Document([
            'project_id' => $project->id,
            'document_type' => 'Validation_PDF',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_size' => Storage::size($filePath),
            'mime_type' => 'application/pdf',
        ]);

        $document->save();

        return $document;
    }

    /**
     * Send submission confirmation notification
     *
     * @param Project $project
     * @return void
     */
    private function sendSubmissionConfirmation(Project $project): void
    {
        $email = $project->promoter->email;

        Mail::to($email)->send(new ProjectSubmissionConfirmation($project));

        $notification = new Notification([
            'project_id' => $project->id,
            'notification_type' => 'submission_confirmation',
            'channel' => 'email',
            'recipient' => $email,
            'content' => 'Your project has been successfully submitted.',
            'is_sent' => true,
            'sent_date' => now(),
        ]);

        $notification->save();
    }

    /**
     * Send validation notification
     *
     * @param Project $project
     * @return void
     */
    private function sendValidationNotification(Project $project): void
    {
        $email = $project->promoter->email;

        Mail::to($email)->send(new ProjectValidationNotification($project));

        $notification = new Notification([
            'project_id' => $project->id,
            'notification_type' => 'project_validation',
            'channel' => 'email',
            'recipient' => $email,
            'content' => 'Your project has been approved.',
            'is_sent' => true,
            'sent_date' => now(),
        ]);

        $notification->save();
    }

    /**
     * Send rejection notification
     *
     * @param Project $project
     * @return void
     */
    private function sendRejectionNotification(Project $project): void
    {
        $email = $project->promoter->email;

        Mail::to($email)->send(new ProjectRejectionNotification($project));

        $notification = new Notification([
            'project_id' => $project->id,
            'notification_type' => 'project_rejection',
            'channel' => 'email',
            'recipient' => $email,
            'content' => 'Your project has been rejected.',
            'is_sent' => true,
            'sent_date' => now(),
        ]);

        $notification->save();
    }

    /**
     * Log an activity
     *
     * @param int $accountId
     * @param string $action
     * @param string $entity
     * @param int $entityId
     * @param array $details
     * @return void
     */
    private function logActivity(int $accountId, string $action, string $entity, int $entityId, array $details = []): void
    {
        $activity = new \App\Models\ActivityLog([
            'account_id' => $accountId,
            'action' => $action,
            'entity' => $entity,
            'entity_id' => $entityId,
            'details' => $details ? json_encode($details) : null,
            'ip_address' => request()->ip(),
        ]);

        $activity->save();
    }
}
