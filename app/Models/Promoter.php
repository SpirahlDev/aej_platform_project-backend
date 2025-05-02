<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promoter extends Model
{
    use HasFactory;

    protected $fillable = [
        'id_card_number',
        'additional_info',
        'birth_date',
        'birth_place',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
    ];

    protected $casts = [
        'additional_info' => 'array',
    ];


    /**
     * Get the projects for the promoter.
     */
    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
