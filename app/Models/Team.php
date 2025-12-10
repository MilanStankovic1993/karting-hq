<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    /*
     * Odnos: jedan tim ima više korisnika
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /*
     * Jedan tim ima više vozača
     */
    public function drivers()
    {
        return $this->hasMany(Driver::class);
    }

    /*
     * Jedan tim ima više trka
     */
    public function races()
    {
        return $this->hasMany(Race::class);
    }

    /*
     * Jedan tim ima više setup sheets
     */
    public function setupSheets()
    {
        return $this->hasMany(SetupSheet::class);
    }
}
