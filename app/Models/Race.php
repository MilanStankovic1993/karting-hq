<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'track',
        'date',
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
        static::creating(function (Race $race) {
            if (Auth::check()) {
                $race->created_by_id ??= Auth::id();
                $race->updated_by_id ??= Auth::id();
                $race->team_id ??= Auth::user()?->team_id;
            }
        });

        static::updating(function (Race $race) {
            if (Auth::check()) {
                $race->updated_by_id = Auth::id();
            }
        });
    }
}
