<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use HasRoles; // iz spatie/laravel-permission

    /**
     * App-level role konstante (vezane za kolonu users.role)
     */
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
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    /*
     * Tenant tim kojem user pripada
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /*
     * Helperi zbog čitljivosti koda
     */
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

    /*
     * Ako želiš, možeš imati i relaciju ka setup sheetovima koje je kreirao:
     */
    public function createdSetupSheets()
    {
        return $this->hasMany(SetupSheet::class, 'created_by_id');
    }
}
