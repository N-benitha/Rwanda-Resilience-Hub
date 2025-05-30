<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SensorData extends Model
{
    use HasFactory;

    protected $table = 'sensor_data';

    protected $fillable = [
        'sensor_id',
        'sensor_type',
        'location_name',
        'latitude',
        'longitude',
        'water_level',
        'flow_rate',
        'ph_level',
        'turbidity',
        'temperature',
        'battery_level',
        'signal_strength',
        'status',
        'recorded_at'
    ];

    protected $casts = [
        'latitude' => 'decimal:6',
        'longitude' => 'decimal:6',
        'water_level' => 'decimal:3',
        'flow_rate' => 'decimal:3',
        'ph_level' => 'decimal:2',
        'turbidity' => 'decimal:2',
        'temperature' => 'decimal:2',
        'battery_level' => 'integer',
        'signal_strength' => 'integer',
        'recorded_at' => 'datetime'
    ];

    const SENSOR_TYPES = [
        'water_level',
        'flow_rate',
        'ph_monitor',
        'turbidity_sensor',
        'temperature_sensor',
        'multi_parameter'
    ];

    const STATUS_OPTIONS = [
        'active',
        'inactive',
        'maintenance',
        'error',
        'low_battery'
    ];

    public function scopeForLocation($query, string $location)
    {
        return $query->where('location_name', $location);
    }

    public function scopeBySensorType($query, string $type)
    {
        return $query->where('sensor_type', $type);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('recorded_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('recorded_at', '>=', Carbon::now()->subHours($hours));
    }

    public function scopeWithHighWaterLevel($query, float $threshold = 2.0)
    {
        return $query->where('water_level', '>=', $threshold);
    }

    public function scopeLowBattery($query, int $threshold = 20)
    {
        return $query->where('battery_level', '<=', $threshold);
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'active' => 'green',
            'inactive' => 'gray',
            'maintenance' => 'yellow',
            'error' => 'red',
            'low_battery' => 'orange',
            default => 'gray'
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'active' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'inactive' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'maintenance' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
            'error' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            'low_battery' => 'bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
        };
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function needsMaintenance(): bool
    {
        return $this->status === 'maintenance' || 
               $this->battery_level <= 20 || 
               $this->signal_strength <= 30;
    }

    public function hasHighWaterLevel(float $threshold = 2.0): bool
    {
        return $this->water_level >= $threshold;
    }

    public function hasAbnormalReadings(): bool
    {
        return $this->ph_level < 6.0 || 
               $this->ph_level > 8.5 || 
               $this->turbidity > 50.0 ||
               $this->water_level > 3.0;
    }

    public function getSensorTypeDisplayAttribute()
    {
        return match ($this->sensor_type) {
            'water_level' => 'Water Level Sensor',
            'flow_rate' => 'Flow Rate Sensor',
            'ph_monitor' => 'pH Monitor',
            'turbidity_sensor' => 'Turbidity Sensor',
            'temperature_sensor' => 'Temperature Sensor',
            'multi_parameter' => 'Multi-Parameter Sensor',
            default => ucfirst(str_replace('_', ' ', $this->sensor_type))
        };
    }

    public function floodRisks()
    {
        return $this->hasMany(FloodRisk::class, 'location_name', 'location_name');
    }
}