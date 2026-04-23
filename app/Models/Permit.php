<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Permit extends Model
{
    protected $fillable = [
        'plate',
        'holder',
        'type',
        'valid_from',
        'valid_to',
        'status',
        'qr_token',
    ];

    protected static function booted()
    {
        static::creating(function ($permit) {
            if (empty($permit->qr_token)) {
                $permit->qr_token = Str::uuid();
            }
        });
    }
}