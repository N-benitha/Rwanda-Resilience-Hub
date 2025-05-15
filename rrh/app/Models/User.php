<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'location',
        'user_type',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /** Check if the user is an admin
     * 
     * @return bool
    */

    public function isAdmin()
    {
        return $this->user_type === 'admin';
    }

    /** Check if the user is an a government official
     * 
     * @return bool
    */

    public function isGovernment()
    {
        return $this->user_type === 'government';
    }

    /** Check if the user is a civilian
     * 
     * @return bool
    */

    public function isCivilian()
    {
        return $this->user_type === 'civilian';
    }

    /**
     * Get all sensors associated with this user
     */
    public function sensors()
    {
        return $this->hasMany(Sensor::class);
    }

    /**
     * Get all reports created by this user
     */
    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    /**
     * Get all flood predictions created by this user
     */
    public function predictions()
    {
        return $this->hasMany(FloodPrediction::class);
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];
}
