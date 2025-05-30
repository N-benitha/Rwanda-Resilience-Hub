<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class FloodRisk extends Model
{
    use HasFactory;

    protected $fillable = [
        'location_name',
        'date',
        'risk_level',
        'probability',
        'severity',
        'factors',
        'recommendations',
        'precipitation_7day',
        'temperature_avg',
        'humidity_avg',
        'calculated_at'
    ];

    protected $casts = [
        'date' => 'date',
        'probability' => 'decimal:2',
        'severity' => 'decimal:2',
        'factors' => 'array',
        'recommendations' => 'array',
        'precipitation_7day' => 'decimal:2',
        'temperature_avg' => 'decimal:2',
        'humidity_avg' => 'decimal:2',
        'calculated_at' => 'datetime'
    ];

    const RISK_LEVELS = [
        'low' => 1,
        'moderate' => 2,
        'high' => 3,
        'extreme' => 4
    ];

    const SEVERITY_LEVELS = [
        'minor' => 1,
        'moderate' => 2,
        'significant' => 3,
        'severe' => 4,
        'catastrophic' => 5
    ];

    public function scopeForLocation($query, string $location)
    {
        return $query->where('location_name', $location);
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'extreme']);
    }

    public function scopeRecent($query, int $days = 7)
    {
        return $query->where('date', '>=', Carbon::now()->subDays($days));
    }

    public function getRiskLevelNumericAttribute()
    {
        return self::RISK_LEVELS[$this->risk_level] ?? 0;
    }

    public function getSeverityNumericAttribute()
    {
        return self::SEVERITY_LEVELS[$this->severity] ?? 0;
    }

    public function getRiskColorAttribute()
    {
        return match ($this->risk_level) {
            'low' => 'green',
            'moderate' => 'yellow',
            'high' => 'orange',
            'extreme' => 'red',
            default => 'gray'
        };
    }

    public function getRiskBadgeClassAttribute()
    {
        return match ($this->risk_level) {
            'low' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'moderate' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'high' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            'extreme' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
        };
    }

    public function isHighRisk(): bool
    {
        return in_array($this->risk_level, ['high', 'extreme']);
    }

    public function isProbabilityHigh(): bool
    {
        return $this->probability >= 70.0;
    }

    public function getMainFactorsAttribute()
    {
        return array_slice($this->factors ?? [], 0, 3);
    }

    public function getPrimaryRecommendationsAttribute()
    {
        return array_slice($this->recommendations ?? [], 0, 5);
    }

    public function weatherData()
    {
        return $this->hasMany(WeatherData::class, 'location_name', 'location_name');
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }
}