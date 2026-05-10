<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermitHolder extends Model
{
    protected $fillable = [
        'nome',
        'cognome',
        'email',
        'telefono',
        'codice_fiscale',
        'note',
    ];

    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }

    public function getFullNameAttribute(): string
    {
        // Se cognome è null, ritorna solo il nome (strutture)
        // Se cognome è presente, ritorna cognome + nome (privati)
        if ($this->cognome) {
            return trim($this->cognome . ' ' . $this->nome);
        }
        return $this->nome;
    }
}