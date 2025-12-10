<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'team_id',
        'name',
        'track',
        'date',
        'notes',
    ];
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    public function setupSheets()
    {
        return $this->hasMany(SetupSheet::class);
    }
}
