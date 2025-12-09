<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Race;
use App\Models\Driver;
use App\Models\User;

class SetupSheet extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver',
        'track',
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
        'driver_id',
        'race_id',
        'created_by_id',
        'comments',
    ];

    public function race()
    {
        return $this->belongsTo(Race::class);
    }

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }
}
