<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'document_type',
        'file_name',
        'file_path',
        'file_size',
        'mime_type',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'file_size' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the document.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the URL for the document.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Get the formatted file size.
     *
     * @return string
     */
    public function getFormattedFileSizeAttribute()
    {
        $bytes = $this->file_size;
        
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if document is an image.
     *
     * @return bool
     */
    public function getIsImageAttribute()
    {
        return in_array($this->mime_type, [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/svg+xml',
            'image/webp',
        ]);
    }

    /**
     * Check if document is a PDF.
     *
     * @return bool
     */
    public function getIsPdfAttribute()
    {
        return $this->mime_type === 'application/pdf';
    }

    /**
     * Get the icon for the document based on mime type.
     *
     * @return string
     */
    public function getIconAttribute()
    {
        if ($this->is_pdf) {
            return 'fa-file-pdf';
        } elseif ($this->is_image) {
            return 'fa-file-image';
        } elseif (strpos($this->mime_type, 'word') !== false) {
            return 'fa-file-word';
        } elseif (strpos($this->mime_type, 'excel') !== false || strpos($this->mime_type, 'spreadsheet') !== false) {
            return 'fa-file-excel';
        } else {
            return 'fa-file';
        }
    }
}
