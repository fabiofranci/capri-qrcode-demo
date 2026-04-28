<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Permit;
use App\Models\PermitHolder;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermitTest extends TestCase
{
    use RefreshDatabase;

    public function test_is_valid_when_in_date_range_and_active()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'status' => 'active',
        ]);

        $this->assertTrue($permit->isValid());
    }

    public function test_is_not_valid_if_expired()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->subDays(5),
            'valid_to' => now()->subDay(),
        ]);

        $this->assertFalse($permit->isValid());
    }

    public function test_is_not_valid_if_revoked()
    {
        $permit = Permit::factory()->create([
            'status' => 'revoked',
        ]);

        $this->assertFalse($permit->isValid());
    }

    public function test_detects_expired()
    {
        $permit = Permit::factory()->create([
            'valid_to' => now()->subDay(),
        ]);

        $this->assertTrue($permit->isExpired());
    }

    public function test_generates_qr_token_if_missing()
    {
        $permit = Permit::factory()->create([
            'qr_token' => null,
        ]);

        $this->assertNotNull($permit->qr_token);
    }

    public function test_syncs_holder_and_plate_from_relations_on_save()
    {
        $holder = PermitHolder::create([
            'nome' => 'Mario',
            'cognome' => 'Rossi',
        ]);

        $vehicle = Vehicle::create([
            'permit_holder_id' => $holder->id,
            'targa' => 'AA123BB',
            'marca' => 'Fiat',
            'modello' => 'Panda',
            'colore' => 'Bianco',
        ]);

        $permit = Permit::factory()->create([
            'permit_holder_id' => $holder->id,
            'vehicle_id' => $vehicle->id,
            'holder' => null,
            'plate' => null,
        ]);

        $this->assertSame('Mario', $permit->holder);
        $this->assertSame('AA123BB', $permit->plate);
        $this->assertSame('Mario', $permit->holder_name);
    }
}