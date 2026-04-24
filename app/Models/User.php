<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements FilamentUser
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isOperatore(): bool
    {
        return $this->role === 'operatore';
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }
}