<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class WeatherData extends Model
{
    use HasFactory;

    protected $table = 'weather_data';

    protected $fillable = [
        'location_name',
        'latitude',
        'longitude',
        'temperature',
        'humidity',
        'pressure',
        'precipitation',
        'wind_speed',
        'wind_direction',
        'visibility',
        'weather_condition',
        'weather_description',
        'cloud_cover',
        'uv_index',
        'solar_radiation',
        'recorded_at',
        'api_source'
    ];

    protected $casts = [
        'temperature' => 'decimal:2',
        'humidity' => 'integer',
        'pressure' => 'decimal:2',
        'precipitation' => 'decimal:2',
        'wind_speed' => 'decimal:2',
        'wind_direction' => 'integer',
        'visibility' => 'decimal:2',
        'cloud_cover' => 'integer',
        'uv_index' => 'decimal:1',
        'solar_radiation' => 'decimal:2',
        'recorded_at' => 'datetime',
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6'
    ];

    public function scopeForLocation($query, string $location)
    {
        return $query->where('location_name', $location);
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', Carbon::now()->subHours($hours));
    }

    public function scopeWithHighPrecipitation($query, float $threshold = 10.0)
    {
        return $query->where('precipitation', '>=', $threshold);
    }

    public function getTemperatureFahrenheitAttribute()
    {
        return ($this->temperature * 9/5) + 32;
    }

    public function getPressureInHgAttribute()
    {
        return $this->pressure * 0.02953;
    }

    public function getWindSpeedMphAttribute()
    {
        return $this->wind_speed * 2.237;
    }

    public function getWindDirectionTextAttribute()
    {
        $directions = [
            'N', 'NNE', 'NE', 'ENE', 'E', 'ESE', 'SE', 'SSE',
            'S', 'SSW', 'SW', 'WSW', 'W', 'WNW', 'NW', 'NNW'
        ];
        
        $index = round($this->wind_direction / 22.5) % 16;
        return $directions[$index];
    }

    public function isHighRiskCondition(): bool
    {
        return $this->precipitation >= 20.0 || 
               $this->wind_speed >= 15.0 || 
               $this->humidity >= 90;
    }

    public function floodRisks()
    {
        return $this->hasMany(FloodRisk::class, 'location_name', 'location_name');
    }
}