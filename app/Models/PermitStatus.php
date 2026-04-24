<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PermitStatus extends Model
{
    protected $fillable = [
        'name',
        'color',
        'sort',
    ];

    public function permits(): HasMany
    {
        return $this->hasMany(Permit::class);
    }
}