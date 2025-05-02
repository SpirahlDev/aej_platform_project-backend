<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    use HasFactory;
    protected $table="persons";

    protected $fillable = [
        'last_name',
        'first_name',
        'email',
        'phone',
        'address',
    ];

    /**
     * Get the account associated with the person.
     */
    public function account()
    {
        return $this->hasOne(Account::class);
    }

    /**
     * Get the promoter associated with the person.
     */
    public function promoter()
    {
        return $this->hasOne(Promoter::class);
    }

    /**
     * Get the full name of the person.
     */
    public function getFullNameAttribute()
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
