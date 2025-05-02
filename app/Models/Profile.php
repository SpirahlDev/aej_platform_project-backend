<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'permissions',
    ];

    protected $casts = [
        'permissions' => 'array',
    ];

    /**
     * Get the accounts for the profile.
     */
    public function accounts()
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Check if the profile has a specific permission.
     */
    public function hasPermission($permission)
    {
        return in_array($permission, $this->permissions ?? []);
    }
}