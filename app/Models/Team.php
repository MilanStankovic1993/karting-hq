<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',

        // nova polja
        'created_by_id',
        'updated_by_id',
        'is_active',
        'subscription_started_at',
        'subscription_expires_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'subscription_started_at' => 'datetime',
        'subscription_expires_at' => 'datetime',
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

    /*
     * Ko je kreirao tim
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /*
     * Ko je poslednji menjao tim
     */
    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by_id');
    }

    /*
     * Automatsko popunjavanje created_by_id / updated_by_id
     */
    protected static function booted(): void
    {
        static::creating(function (Team $team) {
            if (Auth::check()) {
                if (! $team->created_by_id) {
                    $team->created_by_id = Auth::id();
                }
                if (! $team->updated_by_id) {
                    $team->updated_by_id = Auth::id();
                }
            }

            // ako tim postaje aktivan a subscription start nije setovan
            if ($team->is_active && ! $team->subscription_started_at) {
                $team->subscription_started_at = now();
            }
        });

        static::updating(function (Team $team) {
            if (Auth::check()) {
                $team->updated_by_id = Auth::id();
            }
        });
    }
}
