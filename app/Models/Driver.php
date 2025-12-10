<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'short_name',
        'team',
        'kart_number',
        'active',
        'notes',
        'created_by_id',
        'updated_by_id',
    ];

    /* -----------------------------------
     |  RELATIONS
     |----------------------------------- */

    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function setupSheets()
    {
        return $this->hasMany(SetupSheet::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /* -----------------------------------
     |  AUTO-FILL created_by / updated_by
     |----------------------------------- */
    protected static function booted(): void
    {
        static::creating(function (Driver $driver) {
            if (Auth::check()) {
                $driver->created_by_id ??= Auth::id();
                $driver->updated_by_id ??= Auth::id();
                $driver->team_id ??= Auth::user()?->team_id;
            }
        });

        static::updating(function (Driver $driver) {
            if (Auth::check()) {
                $driver->updated_by_id = Auth::id();
            }
        });
    }
}
