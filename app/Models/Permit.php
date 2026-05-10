<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Permit extends Model
{
    use HasFactory;

    protected $fillable = [

        // snapshot legacy
        'plate',
        'holder',

        // dati permesso
        'type',
        'status',

        // relazioni
        'permit_holder_id',
        'vehicle_id',
        'permit_status_id',

        // validità
        'valid_from',
        'valid_to',

        // qr
        'qr_token',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
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
    | ACCESSOR
    |--------------------------------------------------------------------------
    */

    public function getPlateAttribute($value): ?string
    {
        return $this->attributes['plate'] ?? $value;
    }

    public function getHolderAttribute($value): ?string
    {
        return $this->attributes['holder'] ?? $value;
    }

    public function getHolderNameAttribute(): ?string
    {
        if ($this->permitHolder) {

            return trim(
                ($this->permitHolder->cognome ?? '') . ' ' .
                ($this->permitHolder->nome ?? '')
            );
        }

        return $this->attributes['holder'] ?? null;
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->statusRel?->name ?? $this->status;
    }

    /*
    |--------------------------------------------------------------------------
    | VALIDAZIONE
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
        if (!$this->valid_to) {
            return false;
        }

        return now()->gt($this->valid_to);
    }

    public static function inScadenzaProssimiGiorni(int $giorni = 30)
    {
        return self::query()
            ->whereNotNull('valid_to')
            ->whereBetween('valid_to', [
                Carbon::today(),
                Carbon::today()->addDays($giorni),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | BOOT
    |--------------------------------------------------------------------------
    */

    protected static function booted(): void
    {
        static::creating(function (Permit $permit) {

            if (empty($permit->qr_token)) {
                $permit->qr_token = (string) Str::uuid();
            }

        });

        static::saving(function (Permit $permit) {

            $permit->syncSnapshotFields();

        });
    }

    /*
    |--------------------------------------------------------------------------
    | SNAPSHOT
    |--------------------------------------------------------------------------
    */

    public function syncSnapshotFields(): void
    {
        /*
        |--------------------------------------------------------------------------
        | HOLDER SNAPSHOT
        |--------------------------------------------------------------------------
        */

        if ($this->vehicle_id) {

            $vehicle = $this->vehicle
                ?? Vehicle::with('permitHolder')->find($this->vehicle_id);

            if ($vehicle) {

                $this->plate = $vehicle->targa;

                if ($vehicle->permitHolder) {

                    $this->holder = trim(
                        ($vehicle->permitHolder->cognome ?? '') . ' ' .
                        ($vehicle->permitHolder->nome ?? '')
                    );
                }
            }
        }

        /*
        |--------------------------------------------------------------------------
        | FALLBACK HOLDER
        |--------------------------------------------------------------------------
        */

        if (
            empty($this->holder)
            && $this->permit_holder_id
        ) {

            $holder = $this->permitHolder
                ?? PermitHolder::find($this->permit_holder_id);

            if ($holder) {

                $this->holder = trim(
                    ($holder->cognome ?? '') . ' ' .
                    ($holder->nome ?? '')
                );
            }
        }
    }
}