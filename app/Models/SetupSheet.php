<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SetupSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'race_id',
        'driver_id',
        'created_by_id',
        'updated_by_id',
        'date',
        'time_label',
        'chassis',
        'carb',
        'engine',
        'sprocket',
        'exhaust',
        'spacer',
        'axle',
        'front_bar',
        'ch_positions',
        'caster',
        'camber',
        'tyres_type',
        'front_entry',
        'front_mid',
        'front_exit',
        'rear_entry',
        'rear_mid',
        'rear_exit',
        'engine_low',
        'engine_mid',
        'engine_top',
        'temperature',
        'fastest_lap',
        'comments',
    ];

    /* -----------------------------------
     |  RELATIONS
     |----------------------------------- */

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function team()
    {
        return $this->belongsTo(Team::class);
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
        static::creating(function (SetupSheet $sheet) {
            if (Auth::check()) {
                $sheet->created_by_id ??= Auth::id();
                $sheet->updated_by_id ??= Auth::id();
                $sheet->team_id ??= Auth::user()?->team_id;
            }
        });

        static::updating(function (SetupSheet $sheet) {
            if (Auth::check()) {
                $sheet->updated_by_id = Auth::id();
            }
        });
    }
}
