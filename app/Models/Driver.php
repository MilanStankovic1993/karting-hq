<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'short_name',
        'team',
        'kart_number',
        'active',
        'notes',
    ];

    public function setupSheets()
    {
        return $this->hasMany(SetupSheet::class);
    }
}
