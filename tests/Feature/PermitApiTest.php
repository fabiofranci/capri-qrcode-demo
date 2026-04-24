<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Permit; // ✅ QUESTA RIGA MANCAVA
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermitApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_example(): void
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    public function test_it_returns_valid_permit()
    {
        $permit = Permit::factory()->create([
            'status' => 'active',
            'valid_from' => now()->subDay(),    
            'valid_to' => now()->addDay(),
        ]);

        $response = $this->getJson("/api/verify/{$permit->qr_token}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'valid',
        ]);
    }

    public function test_it_returns_invalid_if_expired()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->subDays(5),
            'valid_to' => now()->subDay(),
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/verify/{$permit->qr_token}");

        $response->assertStatus(200);
        $response->assertJson([
            'status' => 'invalid',
            "reason"=>"expired",

        ]);
    }

    public function test_it_returns_invalid_if_revoked()
    {
        $permit = Permit::factory()->create([
            'status' => 'revoked',
        ]);

        $response = $this->getJson("/api/verify/{$permit->qr_token}");

        $response->assertJson([
            'status' => 'invalid',
            "reason"=>"revoked",

        ]);
    }

    public function test_it_returns_not_found_if_not_found()
    {
        $response = $this->getJson("/api/verify/invalid-token");

        $response->assertJson([
            'status' => 'invalid',
            "reason"=>"not_found",
        ]);
    }
    
    public function test_it_is_invalid_if_not_started_yet()
    {
        $permit = Permit::factory()->create([
            'valid_from' => now()->addDay(),
            'valid_to' => now()->addDays(5),
            'status' => 'active',
        ]);

        $response = $this->getJson("/api/verify/{$permit->qr_token}");

        $response->assertJson([
            'status' => 'invalid',
            'reason' => 'not_started',
        ]);
    }    

}