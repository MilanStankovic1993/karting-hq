<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;
    use Notifiable;
    use HasRoles;

    public const ROLE_SUPER_ADMIN = 'SUPER_ADMIN';
    public const ROLE_TECHNICIAN  = 'TECHNICIAN';
    public const ROLE_WORKER      = 'WORKER';

    protected $fillable = [
        'name',
        'role',
        'username',
        'email',
        'password',
        'team_id',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function canAccessPanel(Panel $panel): bool
    {
        // ako imaš više panela, pusti samo admin
        if ($panel->getId() !== 'admin') {
            return false;
        }

        // super admin uvek može
        if ($this->role === self::ROLE_SUPER_ADMIN) {
            return true;
        }

        // ostali: user mora biti aktivan
        if (! $this->is_active) {
            return false;
        }

        // i tim mora biti aktivan ako postoji team_id
        if ($this->team_id) {
            return (bool) ($this->team?->is_active);
        }

        // bez tima (a nije super admin) -> nema pristup panelu
        return false;
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === self::ROLE_SUPER_ADMIN;
    }

    public function isTechnician(): bool
    {
        return $this->role === self::ROLE_TECHNICIAN;
    }

    public function isWorker(): bool
    {
        return $this->role === self::ROLE_WORKER;
    }

    public function createdSetupSheets()
    {
        return $this->hasMany(SetupSheet::class, 'created_by_id');
    }
}
