<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class User extends Authenticatable implements FilamentUser
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;
    use HasRoles;
    use HasPanelShield;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function canAccessPanel(Panel $panel): bool
    {
        if (
            $panel->getId() === 'admin'
            && $this->hasRole('admin') === false
            && $this->hasRole('super-admin') === false
        ) {
            return false;
        }

        return true;
    }

    public function getRoleNamesAttribute(): string
    {
        return $this->roles->pluck('name')->join(',');
    }

    public function properties(): BelongsToMany
    {
        return $this->belongsToMany(MonitoredProperty::class, 'user_property', 'user_id', 'monitored_property_id');
    }
}
