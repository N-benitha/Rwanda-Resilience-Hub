<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SensorReading extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'sensor_id',
        'rainfall',
        'temperature',
        'humidity',
        'wind_speed',
        'image_path',
        'video_path',
    ];

    /**
     * Get the sensor that this reading belongs to
     */
    public function sensor()
    {
        return $this->belongsTo(Sensor::class);
    }
}
