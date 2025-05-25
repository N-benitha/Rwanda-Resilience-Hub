<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Report extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'parameters',
        'data',
        'summary',
        'file_path',
        'status',
        'error_message',
        'user_id',
        'generated_at'
    ];

    protected $casts = [
        'parameters' => 'array',
        'data' => 'array',
        'generated_at' => 'datetime'
    ];

    const REPORT_TYPES = [
        'weekly_flood_risk',
        'monthly_weather_summary',
        'location_risk_assessment',
        'comparative_analysis',
        'sensor_performance',
        'emergency_response'
    ];

    const STATUS_OPTIONS = [
        'pending',
        'processing',
        'completed',
        'failed'
    ];

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeInDateRange($query, Carbon $startDate, Carbon $endDate)
    {
        return $query->whereBetween('generated_at', [$startDate, $endDate]);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('generated_at', '>=', Carbon::now()->subDays($days));
    }

    public function getStatusColorAttribute()
    {
        return match ($this->status) {
            'pending' => 'gray',
            'processing' => 'blue',
            'completed' => 'green',
            'failed' => 'red',
            default => 'gray'
        };
    }

    public function getStatusBadgeClassAttribute()
    {
        return match ($this->status) {
            'pending' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200',
            'processing' => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
            'completed' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
            'failed' => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
            default => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200'
        };
    }

    public function getTypeDisplayAttribute()
    {
        return match ($this->type) {
            'weekly_flood_risk' => 'Weekly Flood Risk Report',
            'monthly_weather_summary' => 'Monthly Weather Summary',
            'location_risk_assessment' => 'Location Risk Assessment',
            'comparative_analysis' => 'Comparative Analysis',
            'sensor_performance' => 'Sensor Performance Report',
            'emergency_response' => 'Emergency Response Report',
            default => ucfirst(str_replace('_', ' ', $this->type))
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isFailed(): bool
    {
        return $this->status === 'failed';
    }

    public function isProcessing(): bool
    {
        return $this->status === 'processing';
    }

    public function hasFile(): bool
    {
        return !empty($this->file_path) && file_exists(storage_path('app/' . $this->file_path));
    }

    public function getFileSizeAttribute()
    {
        if (!$this->hasFile()) {
            return null;
        }

        $bytes = filesize(storage_path('app/' . $this->file_path));
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExtensionAttribute()
    {
        if (!$this->file_path) {
            return null;
        }
        
        return pathinfo($this->file_path, PATHINFO_EXTENSION);
    }

    public function getParameterValue(string $key, $default = null)
    {
        return $this->parameters[$key] ?? $default;
    }

    public function getDataValue(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function floodRisk()
    {
        return $this->belongsTo(FloodRisk::class);
    }
}