<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FloodPrediction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'location',
        'risk_percentage',
        'rainfall_data',
        'river_level_data',
        'soil_moisture_data',
        'prediction_date',
        'validity_period',
        'is_correct',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'risk_percentage' => 'float',
        'rainfall_data' => 'json',
        'river_level_data' => 'json',
        'soil_moisture_data' => 'json',
        'prediction_date' => 'datetime',
        'validity_period' => 'integer',
        'is_correct' => 'boolean',
    ];

    /**
     * Get the user that created this prediction
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}