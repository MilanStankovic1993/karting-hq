<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
