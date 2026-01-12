<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SetupSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        // ownership / scope
        'team_id',
        'race_id',
        'driver_id',
        'created_by_id',
        'updated_by_id',

        // header
        'date',
        'time_label',

        // kart setup
        'chassis',
        'carb',
        'engine',
        'sprocket',
        'exhaust',
        'spacer',
        'axle',
        'front_bar',

        // ✅ CH POSITION (FRONT / REAR)
        'ch_position_front',
        'ch_position_rear',

        // geometry
        'camber',
        'caster',

        'tyres_type',

        // tyre pressures (cold)
        'pressure_cold_fl',
        'pressure_cold_fr',
        'pressure_cold_rl',
        'pressure_cold_rr',

        // tyre pressures (hot)
        'pressure_hot_fl',
        'pressure_hot_fr',
        'pressure_hot_rl',
        'pressure_hot_rr',

        // balance / handling (sliders -3..3)
        'front_entry',
        'front_mid',
        'front_exit',
        'rear_entry',
        'rear_mid',
        'rear_exit',

        // engine needles (sliders -3..3)
        'engine_low',
        'engine_mid',
        'engine_top',

        // results
        'temperature',
        'lap_time',
        'fastest_lap',
        'comments',
    ];

    protected $casts = [
        'date' => 'date',

        // tyre pressures (stored as 0.66, UI shows 66)
        'pressure_cold_fl' => 'decimal:2',
        'pressure_cold_fr' => 'decimal:2',
        'pressure_cold_rl' => 'decimal:2',
        'pressure_cold_rr' => 'decimal:2',

        'pressure_hot_fl' => 'decimal:2',
        'pressure_hot_fr' => 'decimal:2',
        'pressure_hot_rl' => 'decimal:2',
        'pressure_hot_rr' => 'decimal:2',

        // sliders (stored as string, used as int)
        'front_entry' => 'integer',
        'front_mid'   => 'integer',
        'front_exit'  => 'integer',
        'rear_entry'  => 'integer',
        'rear_mid'    => 'integer',
        'rear_exit'   => 'integer',

        'engine_low'  => 'integer',
        'engine_mid'  => 'integer',
        'engine_top'  => 'integer',
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
