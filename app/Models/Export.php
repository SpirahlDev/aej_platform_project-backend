<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Export extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'export_type',
        'file_name',
        'file_path',
        'filter_parameters',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'filter_parameters' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account that created the export.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the URL for the export file.
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return Storage::url($this->file_path);
    }

    /**
     * Check if export is a PDF.
     *
     * @return bool
     */
    public function getIsPdfAttribute()
    {
        return $this->export_type === 'PDF';
    }

    /**
     * Check if export is an Excel file.
     *
     * @return bool
     */
    public function getIsExcelAttribute()
    {
        return $this->export_type === 'XLSX';
    }

    /**
     * Get the icon for the export based on type.
     *
     * @return string
     */
    public function getIconAttribute()
    {
        if ($this->is_pdf) {
            return 'fa-file-pdf';
        } elseif ($this->is_excel) {
            return 'fa-file-excel';
        } else {
            return 'fa-file';
        }
    }

    /**
     * Scope a query to only include PDF exports.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePdf($query)
    {
        return $query->where('export_type', 'PDF');
    }

    /**
     * Scope a query to only include Excel exports.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeExcel($query)
    {
        return $query->where('export_type', 'XLSX');
    }

    /**
     * Delete the export file from storage when the model is deleted.
     */
    protected static function boot()
    {
        parent::boot();

        static::deleting(function (Export $export) {
            Storage::delete($export->file_path);
        });
    }
}
