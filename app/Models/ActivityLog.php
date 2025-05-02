<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'account_id',
        'action',
        'entity',
        'entity_id',
        'details',
        'ip_address',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the account that performed the activity.
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the related model based on entity type.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getRelatedModelAttribute()
    {
        if (!$this->entity || !$this->entity_id) {
            return null;
        }

        $entityModelMap = [
            'projects' => Project::class,
            'promoters' => Promoter::class,
            'accounts' => Account::class,
            'documents' => Document::class,
            'exports' => Export::class,
        ];

        if (!isset($entityModelMap[$this->entity])) {
            return null;
        }

        $modelClass = $entityModelMap[$this->entity];
        return $modelClass::find($this->entity_id);
    }

    /**
     * Scope a query to only include logs for a specific entity.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $entity
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntity($query, $entity)
    {
        return $query->where('entity', $entity);
    }

    /**
     * Scope a query to only include logs for a specific entity ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $entityId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForEntityId($query, $entityId)
    {
        return $query->where('entity_id', $entityId);
    }

    /**
     * Scope a query to only include logs for a specific action.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $action
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Scope a query to only include logs from a specific account.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $accountId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByAccount($query, $accountId)
    {
        return $query->where('account_id', $accountId);
    }

    /**
     * Get the formatted action with better readability.
     *
     * @return string
     */
    public function getFormattedActionAttribute()
    {
        return ucfirst(str_replace('_', ' ', $this->action));
    }

    /**
     * Get the formatted entity with better readability.
     *
     * @return string
     */
    public function getFormattedEntityAttribute()
    {
        return ucfirst(str_replace('_', ' ', rtrim($this->entity, 's')));
    }
}
