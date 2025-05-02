<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProjectType extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'project_type';

    protected $fillable = [
        'name',
        'code',
    ];

    /**
     * Get the projects for the project type.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}