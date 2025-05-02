<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'promoter_id',
        'title',
        'summary',
        'project_type_id',
        'legal_form_id',
        'description',
        'status',
        'rejection_reason',
        'submission_date',
        'review_date',
        'decision_date',
        'validator_account_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'submission_date' => 'datetime',
        'review_date' => 'datetime',
        'decision_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'status' => 'Submitted',
    ];

    /**
     * Get the promoter that owns the project.
     */
    public function promoter()
    {
        return $this->belongsTo(Promoter::class);
    }

    /**
     * Get the project type of this project.
     */
    public function projectType()
    {
        return $this->belongsTo(ProjectType::class);
    }

    /**
     * Get the legal form of this project.
     */
    public function legalForm()
    {
        return $this->belongsTo(LegalForm::class);
    }

    /**
     * Get the account that validated this project.
     */
    public function validator()
    {
        return $this->belongsTo(Account::class, 'validator_account_id');
    }

    /**
     * Get the documents for this project.
     */
    public function documents()
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get the notifications for this project.
     */
    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the AI recommendations for this project.
     */
    public function aiRecommendations()
    {
        return $this->hasOne(AiRecommendation::class);
    }

    /**
     * Scope a query to only include projects with a specific status.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $status
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope a query to only include projects of a specific type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $typeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $typeId)
    {
        return $query->where('project_type_id', $typeId);
    }

    /**
     * Check if the project has a specific document type.
     *
     * @param string $documentType
     * @return bool
     */
    public function hasDocumentType($documentType)
    {
        return $this->documents()->where('document_type', $documentType)->exists();
    }

    /**
     * Get the ID Card document.
     *
     * @return \App\Models\Document|null
     */
    public function getIdCardDocument()
    {
        return $this->documents()->where('document_type', 'ID_Card')->first();
    }

    /**
     * Get the Business Plan document.
     *
     * @return \App\Models\Document|null
     */
    public function getBusinessPlanDocument()
    {
        return $this->documents()->where('document_type', 'Business_Plan')->first();
    }

    /**
     * Get the Validation PDF document.
     *
     * @return \App\Models\Document|null
     */
    public function getValidationPdfDocument()
    {
        return $this->documents()->where('document_type', 'Validation_PDF')->first();
    }

    /**
     * Check if this project is approved.
     *
     * @return bool
     */
    public function isApproved()
    {
        return $this->status === 'Approved';
    }

    /**
     * Check if this project is rejected.
     *
     * @return bool
     */
    public function isRejected()
    {
        return $this->status === 'Rejected';
    }

    /**
     * Check if this project is under review.
     *
     * @return bool
     */
    public function isUnderReview()
    {
        return $this->status === 'Under Review';
    }

    /**
     * Check if this project is just submitted (not yet in review).
     *
     * @return bool
     */
    public function isSubmitted()
    {
        return $this->status === 'Submitted';
    }
}
