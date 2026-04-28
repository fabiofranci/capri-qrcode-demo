<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

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

    protected $appends = [
        'holder_name',
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

    public function getHolderNameAttribute(): ?string
    {
        return $this->permitHolder?->nome ?? $this->attributes['holder'] ?? null;
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

    public function getReasonLabel(): ?string
    {
        return match ($this->getValidationResult()['reason']) {
            'revoked' => 'Permesso revocato',
            'expired' => 'Permesso scaduto',
            'not_started' => 'Permesso non ancora valido',
            default => null,
        };
    }

    public function isValid(): bool
    {
        return $this->getValidationResult()['status'] === 'valid';
    }

    public function isExpired(): bool
    {
        return now()->toDateString() > $this->valid_to->toDateString();
    }

    public static function inScadenzaProssimiGiorni(int $giorni = 30)
    {
        return self::query()
            ->whereNotNull('valido_al')
            ->whereBetween('valido_al', [
                Carbon::today(),
                Carbon::today()->addDays($giorni),
            ]);
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

        static::saving(function (Permit $permit) {
            $permit->syncSnapshotFields();
        });
    }

    public function syncSnapshotFields(): void
    {
        if ($this->permit_holder_id) {
            $this->holder = $this->permitHolder?->nome ?? PermitHolder::find($this->permit_holder_id)?->nome;
        }

        if ($this->vehicle_id) {
            $this->plate = $this->vehicle?->targa ?? Vehicle::find($this->vehicle_id)?->targa;
        }
    }
}
