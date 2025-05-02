<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Account extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'person_id',
        'profile_id',
        'email',
        'password',
        'is_active',
        'last_login',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'last_login' => 'datetime',
    ];

    /**
     * Get the person that owns the account.
     */
    public function person()
    {
        return $this->belongsTo(Person::class);
    }

    /**
     * Get the profile that owns the account.
     */
    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the activity logs for the account.
     */
    public function activityLogs()
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the exports for the account.
     */
    public function exports()
    {
        return $this->hasMany(Export::class);
    }

    /**
     * Get the validated projects for the account.
     */
    public function validatedProjects()
    {
        return $this->hasMany(Project::class, 'validator_account_id');
    }

    public function getPassword(){
        return $this->password;
    }
}
