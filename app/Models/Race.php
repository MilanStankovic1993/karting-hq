<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Race extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'track',
        'date',
        'notes',
    ];

    public function setupSheets()
    {
        return $this->hasMany(SetupSheet::class);
    }
}
