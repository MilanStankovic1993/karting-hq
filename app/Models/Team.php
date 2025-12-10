<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /*
     * Jedan tim ima više vozača
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    /*
     * Jedan tim ima više trka
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function races(): HasMany
    {
        return $this->hasMany(Race::class);
    }

    /*
     * Jedan tim ima više setup sheets
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function setupSheets(): HasMany
    {
        return $this->hasMany(SetupSheet::class);
    }

    /*
     * Ko je kreirao tim
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_id');
    }

    /*
     * Ko je poslednji menjao tim
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updatedBy(): BelongsTo
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

    /* ============================
     * Subscription / access helpers
     * ============================ */

    /**
     * Da li je tim aktivno pretplaćen (datumi postavljeni i expires u budućnosti)
     */
    public function isSubscribed(): bool
    {
        return $this->subscription_started_at !== null
            && $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isFuture();
    }

    /**
     * Da li je pretplata istekla
     */
    public function isExpired(): bool
    {
        return $this->subscription_expires_at !== null
            && $this->subscription_expires_at->isPast();
    }

    /**
     * Da li tim ima pravo pristupa sistemu
     *
     * Pravila:
     * - polje is_active je manualni override koji super-admin koristi
     * - moraju postojati subscription datumi i expires mora biti u budućnosti
     *
     * Trenutno: NULL datumi tretiramo kao "nema pristupa".
     */
    public function hasAccess(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->subscription_started_at === null || $this->subscription_expires_at === null) {
            return false;
        }

        return $this->subscription_expires_at->isFuture();
    }

    /**
     * Accessor: preostali broj dana do isteka (int) ili null ako nema datuma
     */
    public function getRemainingDaysAttribute(): ?int
    {
        if (! $this->subscription_expires_at) {
            return null;
        }

        $now = Carbon::now();
        if ($this->subscription_expires_at->isPast()) {
            return 0;
        }

        return $this->subscription_expires_at->diffInDays($now);
    }

    /* ============================
     * Deactivation helper
     * ============================ */

    /**
     * Deaktiviraj tim i (opciono) sve njegove korisnike.
     *
     * @param  bool  $deactivateUsers  Ako true -> pokuša da postavi is_active = false na usersima.
     * @return void
     */
    public function deactivateWithUsers(bool $deactivateUsers = true): void
    {
        // 1) manualno isključi tim
        $this->is_active = false;
        $this->save();

        if (! $deactivateUsers) {
            return;
        }

        // 2) pokušaj da onemogućiš korisnike tima
        // Ovo radi UPDATE preko query-a (brže), ali zahteva da users tabela ima kolonu is_active.
        try {
            $this->users()->update(['is_active' => false]);
        } catch (\Throwable $e) {
            // fail-safe: samo loguj ako update ne uspe (npr. kolona ne postoji)
            \Log::warning('Team::deactivateWithUsers(): could not set is_active on users for team_id=' . $this->id . '. Exception: ' . $e->getMessage());
        }
    }

    /* ============================
     * Useful scopes
     * ============================ */

    /**
     * Scope: samo aktivni timovi (is_active = true)
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope: timovi sa neisteklom pretplatom (subscription_expires_at > now)
     */
    public function scopeWithValidSubscription($query)
    {
        return $query->whereNotNull('subscription_expires_at')
                     ->where('subscription_expires_at', '>', Carbon::now());
    }
}
