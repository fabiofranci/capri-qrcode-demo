<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'permit_holder_id',
        'targa',
        'marca',
        'modello',
        'colore',
        'note',
    ];

    public function permitHolder(): BelongsTo
    {
        return $this->belongsTo(PermitHolder::class);
    }

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }
}