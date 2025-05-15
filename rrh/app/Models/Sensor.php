<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sensor extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     * 
     * @var array<init, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'location',
        'type',
        'is_connected',
        'last_reading_at',
    ];

    /**
     * The attributes that should be cast.
     * 
     * @var array<string, string>
     */
    protected $casts = [
        'is_connected' => 'boolean',
        'last_reading_at' => 'datetime',
    ];

     /**
     * Get the user that owns the sensor
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all readings for this sensor
     */
    public function readings()
    {
        return $this->hasMany(SensorReading::class);
    }

}