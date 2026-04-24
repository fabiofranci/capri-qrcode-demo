<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permit extends Model
{
    use HasFactory;
    protected $fillable = [
        // legacy
        'plate',
        'holder',
        'type',
        'status',

        // nuove relazioni
        'permit_holder_id',
        'vehicle_id',
        'permit_status_id',

        // date
        'valid_from',
        'valid_to',

        // QR
        'qr_token',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to'   => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | RELAZIONI
    |--------------------------------------------------------------------------
    */

    public function permitHolder(): BelongsTo
    {
        return $this->belongsTo(PermitHolder::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function statusRel(): BelongsTo
    {
        return $this->belongsTo(PermitStatus::class, 'permit_status_id');
    }

    /*
    |--------------------------------------------------------------------------
    | ACCESSOR (retrocompatibilità)
    |--------------------------------------------------------------------------
    */

    public function getPlateAttribute($value)
    {
        return $this->vehicle?->targa ?? $value;
    }

    public function getHolderAttribute($value)
    {
        return $this->permitHolder?->nome ?? $value;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusRel?->name ?? $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | LOGICA
    |--------------------------------------------------------------------------
    */

    public function getValidationResult(): array
    {
        $now = now();

        $status = $this->statusRel?->name ?? $this->status;

        if ($status === 'revoked') {
            return [
                'status' => 'invalid',
                'reason' => 'revoked',
            ];
        }

        if ($this->valid_from && $now->lt($this->valid_from)) {
            return [
                'status' => 'invalid',
                'reason' => 'not_started',
            ];
        }

        if ($this->valid_to && $now->gt($this->valid_to)) {
            return [
                'status' => 'invalid',
                'reason' => 'expired',
            ];
        }

        return [
            'status' => 'valid',
            'reason' => null,
        ];
    }

    public function isValid(): bool
    {
        return $this->getValidationResult()['status'] === 'valid';
    }

    public function isExpired(): bool
    {
        return now()->toDateString() > $this->valid_to->toDateString();
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT (auto token)
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function ($permit) {
            if (empty($permit->qr_token)) {
                $permit->qr_token = (string) Str::uuid();
            }
        });
    }
}