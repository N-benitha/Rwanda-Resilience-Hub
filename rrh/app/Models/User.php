<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens;
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'location',
        'organization',
        'notification_preferences'
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'notification_preferences' => 'array'
    ];

    const USER_TYPES = [
        'admin' => 'Administrator',
        'emergency_manager' => 'Emergency Manager',
        'weather_analyst' => 'Weather Analyst',
        'field_coordinator' => 'Field Coordinator',
        'public_user' => 'Public User'
    ];

    public function scopeByType($query, string $type)
    {
        return $query->where('user_type', $type);
    }

    public function scopeAdmins($query)
    {
        return $query->where('user_type', 'admin');
    }

    public function scopeEmergencyManagers($query)
    {
        return $query->where('user_type', 'emergency_manager');
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isEmergencyManager(): bool
    {
        return $this->user_type === 'emergency_manager';
    }

    public function isWeatherAnalyst(): bool
    {
        return $this->user_type === 'weather_analyst';
    }

    public function isFieldCoordinator(): bool
    {
        return $this->user_type === 'field_coordinator';
    }

    public function isPublicUser(): bool
    {
        return $this->user_type === 'public_user';
    }

    public function canManageUsers(): bool
    {
        return in_array($this->user_type, ['admin', 'emergency_manager']);
    }

    public function canViewAllData(): bool
    {
        return in_array($this->user_type, ['admin', 'emergency_manager', 'weather_analyst']);
    }

    public function canGenerateReports(): bool
    {
        return in_array($this->user_type, ['admin', 'emergency_manager', 'weather_analyst']);
    }

    public function canAccessEmergencyFeatures(): bool
    {
        return in_array($this->user_type, ['admin', 'emergency_manager', 'field_coordinator']);
    }

    public function getUserTypeDisplayAttribute()
    {
        return self::USER_TYPES[$this->user_type] ?? ucfirst(str_replace('_', ' ', $this->user_type));
    }

    public function getNotificationPreference(string $key, $default = true)
    {
        return $this->notification_preferences[$key] ?? $default;
    }

    public function shouldReceiveFloodAlerts(): bool
    {
        return $this->getNotificationPreference('flood_alerts', true);
    }

    public function shouldReceiveWeatherUpdates(): bool
    {
        return $this->getNotificationPreference('weather_updates', true);
    }

    public function shouldReceiveSystemNotifications(): bool
    {
        return $this->getNotificationPreference('system_notifications', true);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function getDefaultNotificationPreferences(): array
    {
        return [
            'flood_alerts' => true,
            'weather_updates' => true,
            'system_notifications' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'emergency_alerts' => true
        ];
    }
}