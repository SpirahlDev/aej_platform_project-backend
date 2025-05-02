<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AiRecommendation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'project_id',
        'viability_score',
        'innovation_score',
        'market_score',
        'recommendations',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'viability_score' => 'decimal:2',
        'innovation_score' => 'decimal:2',
        'market_score' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the project that owns the AI recommendation.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Get the average score across all metrics.
     *
     * @return float
     */
    public function getAverageScoreAttribute()
    {
        $scores = [
            $this->viability_score,
            $this->innovation_score,
            $this->market_score
        ];
        
        // Filter out null values
        $scores = array_filter($scores, function ($score) {
            return !is_null($score);
        });
        
        if (empty($scores)) {
            return null;
        }
        
        return round(array_sum($scores) / count($scores), 2);
    }

    /**
     * Get the recommendation status based on average score.
     *
     * @return string
     */
    public function getStatusAttribute()
    {
        $averageScore = $this->average_score;
        
        if (is_null($averageScore)) {
            return 'Not Available';
        }
        
        if ($averageScore >= 4) {
            return 'Highly Recommended';
        } elseif ($averageScore >= 3) {
            return 'Recommended';
        } elseif ($averageScore >= 2) {
            return 'Neutral';
        } elseif ($averageScore >= 1) {
            return 'Not Recommended';
        } else {
            return 'Strongly Not Recommended';
        }
    }

    /**
     * Get the color code for the recommendation status.
     *
     * @return string
     */
    public function getStatusColorAttribute()
    {
        switch ($this->status) {
            case 'Highly Recommended':
                return 'success';
            case 'Recommended':
                return 'info';
            case 'Neutral':
                return 'warning';
            case 'Not Recommended':
                return 'danger';
            case 'Strongly Not Recommended':
                return 'dark';
            default:
                return 'secondary';
        }
    }

    /**
     * Scope a query to only include recommendations with high scores.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param float $threshold
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeHighPotential($query, $threshold = 4.0)
    {
        return $query->where(function ($query) use ($threshold) {
            $query->where('viability_score', '>=', $threshold)
                  ->orWhere('innovation_score', '>=', $threshold)
                  ->orWhere('market_score', '>=', $threshold);
        });
    }
}
