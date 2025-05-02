<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'notification_type',
        'channel',
        'recipient',
        'content',
        'is_sent',
        'sent_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'is_sent' => 'boolean',
        'sent_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the notification.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope a query to only include sent notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSent($query)
    {
        return $query->where('is_sent', true);
    }

    /**
     * Scope a query to only include unsent notifications.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeUnsent($query)
    {
        return $query->where('is_sent', false);
    }

    /**
     * Scope a query to filter by notification type.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $type
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('notification_type', $type);
    }

    /**
     * Mark the notification as sent.
     *
     * @return bool
     */
    public function markAsSent()
    {
        return $this->update([
            'is_sent' => true,
            'sent_date' => now(),
        ]);
    }

    /**
     * Get the notification type with better formatting.
     *
     * @return string
     */
    public function getFormattedTypeAttribute()
    {
        return ucfirst($this->notification_type);
    }

    /**
     * Get the notification channel with better formatting.
     *
     * @return string
     */
    public function getFormattedChannelAttribute()
    {
        return ucfirst($this->channel);
    }
}
