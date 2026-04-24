<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Permit;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermitTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_is_valid_when_in_date_range_and_active()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->subDay(),
            'valid_to' => now()->addDay(),
            'status' => 'active',
        ]);

        $this->assertTrue($permit->isValid());
    }

    /** @test */
    public function it_is_not_valid_if_expired()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->subDays(5),
            'valid_to' => now()->subDay(),
        ]);

        $this->assertFalse($permit->isValid());
    }

    /** @test */
    public function it_is_not_valid_if_revoked()
    {
        $permit = Permit::factory()->create([
            'status' => 'revoked',
        ]);

        $this->assertFalse($permit->isValid());
    }

    /** @test */
    public function it_detects_expired()
    {
        $permit = Permit::factory()->create([
            'valid_to' => now()->subDay(),
        ]);

        $this->assertTrue($permit->isExpired());
    }

    /** @test */
    public function it_generates_qr_token_if_missing()
    {
        $permit = Permit::factory()->create([
            'qr_token' => null,
        ]);

        $this->assertNotNull($permit->qr_token);
    }
}